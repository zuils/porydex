'use strict';

const app = new Vue({
	el: '#app',
	data: {
		loading: true,
		loaded: false,

		generation: {},
		breadcrumbs: [],
		generations: [],
		pokemon: {},
		abilities: [],
		methods: [],
		versionGroups: [],
		showMoveDescriptionsOption: true,

		showOlderGames: false,
		showMoveDescriptions: true,
	},
	computed: {
		visibleVersionGroups() {
			if (this.showOlderGames) {
				return this.versionGroups;
			}

			return this.versionGroups.filter(vg => vg.generationId === this.generation.id);
		},
		visibleMethods() {
			if (this.showOlderGames) {
				return this.methods;
			}

			return this.methods.filter(m => {
				return this.visibleVersionGroups.some(vg => m.moves.some(mo => mo.vgData[vg.identifier]));
			});
		}
	},
	created() {
		const url = new URL(window.location);

		fetch('/data' + url.pathname, {
			credentials: 'same-origin'
		})
		.then(response => response.json())
		.then(response => {
			this.loading = false;
			this.loaded = true;

			if (response.data) {
				const data = response.data;
				this.generation = data.generation;
				this.breadcrumbs = data.breadcrumbs;
				this.generations = data.generations;
				this.pokemon = data.pokemon;
				this.abilities = data.abilities;
				this.methods = data.methods;
				this.versionGroups = data.versionGroups;
				this.showMoveDescriptionsOption = data.showMoveDescriptions;

				document.title = data.title;

				const showOlderGames = window.localStorage.getItem('dexMoveShowOlderGames') ?? 'false';
				this.showOlderGames = JSON.parse(showOlderGames);

				const showMoveDescriptions = window.localStorage.getItem('dexPokemonShowMoveDescriptions') ?? 'true';
				this.showMoveDescriptions = JSON.parse(showMoveDescriptions);
			}
		});
	},
	methods: {
		toggleOlderGames() {
			this.showOlderGames = !this.showOlderGames;
			window.localStorage.setItem('dexMoveShowOlderGames', this.showOlderGames);
		},
		toggleMoveDescriptions() {
			this.showMoveDescriptions = !this.showMoveDescriptions;
			window.localStorage.setItem('dexPokemonShowMoveDescriptions', this.showMoveDescriptions);
		},
	},
});
