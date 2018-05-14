<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Stats\Usage;

use DateTime;
use Jp\Dex\Domain\Formats\FormatId;

interface UsagePokemonRepositoryInterface
{
	/**
	 * Do any usage Pokémon records exist for this month and format?
	 *
	 * @param DateTime $month
	 * @param FormatId $formatId
	 *
	 * @return bool
	 */
	public function hasAny(DateTime $month, FormatId $formatId) : bool;

	/**
	 * Save a usage Pokémon record.
	 *
	 * @param UsagePokemon $usagePokemon
	 *
	 * @return void
	 */
	public function save(UsagePokemon $usagePokemon) : void;

	/**
	 * Get usage Pokémon records by month and format. Indexed by Pokémon id
	 * value. Use this to recreate a stats usage file, such as
	 * http://www.smogon.com/stats/2014-11/ou-1695.txt.
	 *
	 * @param DateTime $month
	 * @param FormatId $formatId
	 *
	 * @return UsagePokemon[]
	 */
	public function getByMonthAndFormat(DateTime $month, FormatId $formatId) : array;
}
