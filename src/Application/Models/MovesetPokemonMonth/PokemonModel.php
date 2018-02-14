<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Models\MovesetPokemonMonth;

use Jp\Dex\Domain\Forms\FormId;
use Jp\Dex\Domain\Languages\LanguageId;
use Jp\Dex\Domain\Models\Model;
use Jp\Dex\Domain\Models\ModelRepositoryInterface;
use Jp\Dex\Domain\Pokemon\PokemonId;
use Jp\Dex\Domain\Pokemon\PokemonName;
use Jp\Dex\Domain\Pokemon\PokemonNameRepositoryInterface;
use Jp\Dex\Domain\Stats\BaseStatRepositoryInterface;
use Jp\Dex\Domain\Stats\StatId;
use Jp\Dex\Domain\Stats\StatNameRepositoryInterface;
use Jp\Dex\Domain\TypeIcons\TypeIcon;
use Jp\Dex\Domain\TypeIcons\TypeIconRepositoryInterface;
use Jp\Dex\Domain\Types\TypeRepositoryInterface;
use Jp\Dex\Domain\Versions\Generation;

class PokemonModel
{
	/** @var PokemonNameRepositoryInterface $pokemonNameRepository */
	private $pokemonNameRepository;

	/** @var ModelRepositoryInterface $modelRepository */
	private $modelRepository;

	/** @var TypeRepositoryInterface $typeRepository */
	private $typeRepository;

	/** @var TypeIconRepositoryInterface $typeIconRepository */
	private $typeIconRepository;

	/** @var BaseStatRepositoryInterface $baseStatRepository */
	private $baseStatRepository;

	/** @var StatNameRepositoryInterface $statNameRepository */
	private $statNameRepository;


	/** @var PokemonName $pokemon */
	private $pokemonName;

	/** @var Model $model */
	private $model;

	/** @var TypeIcon[] $typeIcons */
	private $typeIcons = [];

	/** @var StatData[] $statDatas */
	private $statDatas = [];

	/**
	 * Constructor.
	 *
	 * @param PokemonNameRepositoryInterface $pokemonNameRepository
	 * @param ModelRepositoryInterface $modelRepository
	 * @param TypeRepositoryInterface $typeRepository
	 * @param TypeIconRepositoryInterface $typeIconRepository
	 * @param BaseStatRepositoryInterface $baseStatRepository
	 * @param StatNameRepositoryInterface $statNameRepository
	 */
	public function __construct(
		PokemonNameRepositoryInterface $pokemonNameRepository,
		ModelRepositoryInterface $modelRepository,
		TypeRepositoryInterface $typeRepository,
		TypeIconRepositoryInterface $typeIconRepository,
		BaseStatRepositoryInterface $baseStatRepository,
		StatNameRepositoryInterface $statNameRepository
	) {
		$this->pokemonNameRepository = $pokemonNameRepository;
		$this->modelRepository = $modelRepository;
		$this->typeRepository = $typeRepository;
		$this->typeIconRepository = $typeIconRepository;
		$this->baseStatRepository = $baseStatRepository;
		$this->statNameRepository = $statNameRepository;
	}

	/**
	 * Set miscellaneous data about the Pokémon (name, types, base stats, etc).
	 *
	 * @param Generation $generation
	 * @param PokemonId $pokemonId
	 * @param LanguageId $languageId
	 *
	 * @return void
	 */
	public function setData(
		Generation $generation,
		PokemonId $pokemonId,
		LanguageId $languageId
	) : void {
		// Get the Pokémon's name.
		$this->pokemonName = $this->pokemonNameRepository->getByLanguageAndPokemon(
			$languageId,
			$pokemonId
		);

		// Get the Pokémon's model.
		$this->model = $this->modelRepository->getByFormAndShinyAndBackAndFemaleAndAttackingIndex(
			new FormId($pokemonId->value()), // A Pokémon's default form has Pokémon id === form id.
			false,
			false,
			false,
			0
		);

		// Get the Pokémon's types.
		$types = $this->typeRepository->getByGenerationAndPokemon(
			$generation,
			$pokemonId
		);

		// Get the type icons of those types.
		$this->typeIcons = [];
		foreach ($types as $slot => $type) {
			$typeIcon = $this->typeIconRepository->getByGenerationAndLanguageAndType(
				$generation,
				$languageId,
				$type->getId()
			);

			$this->typeIcons[$slot] = $typeIcon;
		}

		// Get the Pokémon's base stats.
		$baseStats = $this->baseStatRepository->getByGenerationAndPokemon(
			$generation,
			$pokemonId
		);

		// Get the stat names.
		$statNames = $this->statNameRepository->getByLanguage($languageId);

		// Put the stat data together.
		$statIds = [
			new StatId(StatId::HP),
			new StatId(StatId::ATTACK),
			new StatId(StatId::DEFENSE),
			new StatId(StatId::SPECIAL_ATTACK),
			new StatId(StatId::SPECIAL_DEFENSE),
			new StatId(StatId::SPEED),
		];
		$this->statDatas = [];
		foreach ($statIds as $statId) {
			$this->statDatas[] = new StatData(
				$statNames[$statId->value()]->getName(),
				(int) $baseStats->get($statId)->getValue()
			);
		}
	}

	/**
	 * Get the Pokémon name.
	 *
	 * @return PokemonName
	 */
	public function getPokemonName() : PokemonName
	{
		return $this->pokemonName;
	}

	/**
	 * Get the model.
	 *
	 * @return Model
	 */
	public function getModel() : Model
	{
		return $this->model;
	}

	/**
	 * Get the type icons.
	 *
	 * @return TypeIcon[]
	 */
	public function getTypeIcons() : array
	{
		return $this->typeIcons;
	}

	/**
	 * Get the stat datas.
	 *
	 * @return StatData[]
	 */
	public function getStatDatas() : array
	{
		return $this->statDatas;
	}
}
