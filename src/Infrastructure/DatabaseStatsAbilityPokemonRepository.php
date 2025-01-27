<?php
declare(strict_types=1);

namespace Jp\Dex\Infrastructure;

use DateTime;
use Jp\Dex\Domain\Abilities\AbilityId;
use Jp\Dex\Domain\Formats\FormatId;
use Jp\Dex\Domain\Languages\LanguageId;
use Jp\Dex\Domain\Usage\StatsAbilityPokemon;
use Jp\Dex\Domain\Usage\StatsAbilityPokemonRepositoryInterface;
use Jp\Dex\Domain\Versions\GenerationId;
use PDO;

final class DatabaseStatsAbilityPokemonRepository implements StatsAbilityPokemonRepositoryInterface
{
	public function __construct(
		private PDO $db,
	) {}

	/**
	 * Get stats ability Pokémon by month, format, rating, and ability.
	 *
	 * @return StatsAbilityPokemon[] Ordered by usage percent descending.
	 */
	public function getByMonth(
		DateTime $month,
		?DateTime $prevMonth,
		FormatId $formatId,
		int $rating,
		AbilityId $abilityId,
		GenerationId $generationId,
		LanguageId $languageId
	) : array {
		$prevMonth = $prevMonth !== null
			? $prevMonth->format('Y-m-01')
			: null;

		$stmt = $this->db->prepare(
			'SELECT
				`fi`.`image` AS `icon`,
				`p`.`identifier`,
				`pn`.`name`,
				`urp`.`usage_percent` AS `pokemon_percent`,
				`mra`.`percent` AS `ability_percent`,
				`urp`.`usage_percent` * `mra`.`percent` / 100 AS `usage_percent`,
				`urpp`.`usage_percent` * `mrap`.`percent` / 100 AS `prev_percent`
			FROM `usage_rated_pokemon` as `urp`
			INNER JOIN `moveset_rated_abilities` as `mra`
				ON `urp`.`id` = `mra`.`usage_rated_pokemon_id`
			INNER JOIN `form_icons` AS `fi`
				ON `urp`.`pokemon_id` = `fi`.`form_id`
			INNER JOIN `pokemon` AS `p`
				ON `urp`.`pokemon_id` = `p`.`id`
			INNER JOIN `pokemon_names` AS `pn`
				ON `urp`.`pokemon_id` = `pn`.`pokemon_id`
			LEFT JOIN `usage_rated_pokemon` as `urpp`
				ON `urpp`.`month` = :prev_month
				AND `urp`.`format_id` = `urpp`.`format_id`
				AND `urp`.`rating` = `urpp`.`rating`
				AND `urp`.`pokemon_id` = `urpp`.`pokemon_id`
			LEFT JOIN `moveset_rated_abilities` as `mrap`
				ON `urpp`.`id` = `mrap`.`usage_rated_pokemon_id`
				AND `mra`.`ability_id` = `mrap`.`ability_id`
			WHERE `urp`.`month` = :month
				AND `urp`.`format_id` = :format_id
				AND `urp`.`rating` = :rating
				AND `mra`.`ability_id` = :ability_id
				AND `fi`.`generation_id` = :generation_id
				AND `fi`.`is_female` = 0
				AND `fi`.`is_right` = 0
				AND `pn`.`language_id` = :language_id
			ORDER BY `usage_percent` DESC'
		);
		$stmt->bindValue(':month', $month->format('Y-m-01'));
		$stmt->bindValue(':prev_month', $prevMonth);
		$stmt->bindValue(':format_id', $formatId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
		$stmt->bindValue(':ability_id', $abilityId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':generation_id', $generationId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':language_id', $languageId->value(), PDO::PARAM_INT);
		$stmt->execute();

		$pokemons = [];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$pokemon = new StatsAbilityPokemon(
				$result['icon'],
				$result['identifier'],
				$result['name'],
				(float) $result['pokemon_percent'],
				(float) $result['ability_percent'],
				(float) $result['usage_percent'],
				(float) $result['usage_percent'] - (float) $result['prev_percent']
			);

			$pokemons[] = $pokemon;
		}

		return $pokemons;
	}
}
