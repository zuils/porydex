'use strict';

const app = new Vue({
	el: '#app',
	data: {
		loading: true,
		loaded: false,

		chartTitle: '',
		responseLines: [],
		locale: '',
	},
	computed: {
		chartData() {
			return {
				datasets: this.responseLines.map(l => {
					return {
						label: l.label,
						data: l.data, // This is already in {x: 'YYYY-MM', y: Number} format.
						borderColor: l.color,
						fill: false,
					}
				}),
			};
		},
		chartOptions() {
			return {
				title: {
					display: true,
					text: this.chartTitle,
					fontSize: 16,
				},
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							unit: 'month',
						},
					}],
					yAxes: [{
						ticks: {
							beginAtZero: true
						},
					}],
				},
				tooltips: {
					mode: 'nearest',
					intersect: false,
				},
			};
		},
	},
	mounted() {
		const url = new URL(window.location);
		const encoded = url.searchParams.get('lines');
		const lines = JSON.parse(decodeURIComponent(encoded));

		fetch('/stats/chart', {
			method: 'POST',
			credentials: 'same-origin',
			headers: new Headers({
				'Content-Type': 'application/json'
			}),
			body: JSON.stringify({
				lines: lines,
			}),
		})
		.then(response => response.json())
		.then(async response => {
			this.loading = false;
			this.loaded = true;

			if (response.data) {
				const data = response.data;

				this.chartTitle = data.chartTitle;
				this.responseLines = data.lines;
				this.locale = data.locale;

				document.title = `Porydex - Stats - ${this.chartTitle}`;

				await this.$nextTick();
				this.renderChart();
			}
		});
	},
	methods: {
		renderChart() {
			let ctx = document.getElementById('dex-chart__canvas').getContext('2d');
			this.chart = new Chart(ctx, {
				type: 'line',
				data: this.chartData,
				options: this.chartOptions,
			});
		},
	},
});