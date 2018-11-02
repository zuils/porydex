create table if not exists `base_stats`
(
`generation` tinyint unsigned not null,
`pokemon_id` smallint unsigned not null,
`stat_id` tinyint unsigned not null,

`value` tinyint unsigned not null,

primary key (
	`generation`,
	`pokemon_id`,
	`stat_id`
),
foreign key (`generation`) references `generations` (`generation`)
	on delete restrict
	on update cascade,
foreign key (`pokemon_id`) references `pokemon` (`id`)
	on delete restrict
	on update cascade,
foreign key (`stat_id`) references `stats` (`id`)
	on delete restrict
	on update cascade
) engine = InnoDB;
