<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Stats\Moveset;

use DateTime;
use Jp\Dex\Domain\Formats\FormatId;
use Jp\Dex\Domain\Pokemon\PokemonId;

interface MovesetRatedCounterRepositoryInterface
{
	/**
	 * Save a moveset rated counter record.
	 *
	 * @param MovesetRatedCounter $movesetRatedCounter
	 *
	 * @return void
	 */
	public function save(MovesetRatedCounter $movesetRatedCounter) : void;

	/**
	 * Get moveset rated counter records by month, format, rating, and Pokémon.
	 * Indexed by counter id value.
	 *
	 * @param DateTime $month
	 * @param FormatId $formatId
	 * @param int $rating
	 * @param PokemonId $pokemonId
	 *
	 * @return MovesetRatedCounter[]
	 */
	public function getByMonthAndFormatAndRatingAndPokemon(
		DateTime $month,
		FormatId $formatId,
		int $rating,
		PokemonId $pokemonId
	) : array;
}
