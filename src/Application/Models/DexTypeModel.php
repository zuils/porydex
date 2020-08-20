<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Models;

use Jp\Dex\Domain\Languages\LanguageId;
use Jp\Dex\Domain\Moves\DexMove;
use Jp\Dex\Domain\Moves\DexMoveRepositoryInterface;
use Jp\Dex\Domain\Pokemon\DexPokemon;
use Jp\Dex\Domain\Pokemon\DexPokemonRepositoryInterface;
use Jp\Dex\Domain\Types\DexTypeRepositoryInterface;
use Jp\Dex\Domain\Types\TypeMatchupRepositoryInterface;
use Jp\Dex\Domain\Types\TypeRepositoryInterface;

final class DexTypeModel
{
	private GenerationModel $generationModel;
	private TypeRepositoryInterface $typeRepository;
	private DexTypeRepositoryInterface $dexTypeRepository;
	private TypeMatchupRepositoryInterface $typeMatchupRepository;
	private StatNameModel $statNameModel;
	private DexPokemonRepositoryInterface $dexPokemonRepository;
	private DexMoveRepositoryInterface $dexMoveRepository;


	private array $type = [];
	private array $matchups = [];
	private array $stats = [];

	/** @var DexPokemon[] $pokemon */
	private array $pokemon = [];

	/** @var DexMove[] $moves */
	private array $moves = [];


	/**
	 * Constructor.
	 *
	 * @param GenerationModel $generationModel
	 * @param TypeRepositoryInterface $typeRepository
	 * @param DexTypeRepositoryInterface $dexTypeRepository
	 * @param TypeMatchupRepositoryInterface $typeMatchupRepository
	 * @param StatNameModel $statNameModel
	 * @param DexPokemonRepositoryInterface $dexPokemonRepository
	 * @param DexMoveRepositoryInterface $dexMoveRepository
	 */
	public function __construct(
		GenerationModel $generationModel,
		TypeRepositoryInterface $typeRepository,
		DexTypeRepositoryInterface $dexTypeRepository,
		TypeMatchupRepositoryInterface $typeMatchupRepository,
		StatNameModel $statNameModel,
		DexPokemonRepositoryInterface $dexPokemonRepository,
		DexMoveRepositoryInterface $dexMoveRepository
	) {
		$this->generationModel = $generationModel;
		$this->typeRepository = $typeRepository;
		$this->dexTypeRepository = $dexTypeRepository;
		$this->typeMatchupRepository = $typeMatchupRepository;
		$this->statNameModel = $statNameModel;
		$this->dexPokemonRepository = $dexPokemonRepository;
		$this->dexMoveRepository = $dexMoveRepository;
	}

	/**
	 * Set data for the dex type page.
	 *
	 * @param string $generationIdentifier
	 * @param string $typeIdentifier
	 * @param LanguageId $languageId
	 *
	 * @return void
	 */
	public function setData(
		string $generationIdentifier,
		string $typeIdentifier,
		LanguageId $languageId
	) : void {
		$generationId = $this->generationModel->setByIdentifier($generationIdentifier);

		$type = $this->typeRepository->getByIdentifier($typeIdentifier);

		$this->generationModel->setGensSince($type->getIntroducedInGenerationId());

		$dexType = $this->dexTypeRepository->getById(
			$type->getId(),
			$languageId
		);

		$this->type = [
			'identifier' => $dexType->getIdentifier(),
			'name' => $dexType->getName(),
		];

		// Get the type matchups.
		$this->matchups = ['whenAttacking' => [], 'whenDefending' => []];
		$types = $this->dexTypeRepository->getMainByGeneration($generationId, $languageId);
		$attackingMatchups = $this->typeMatchupRepository->getByAttackingType($generationId, $type->getId());
		$defendingMatchups = $this->typeMatchupRepository->getByDefendingType($generationId, $type->getId());
		foreach ($attackingMatchups as $matchup) {
			$defendingType = $types[$matchup->getDefendingTypeId()->value()];
			$this->matchups['whenAttacking'][] = [
				'type' => $defendingType,
				'multiplier' => $matchup->getMultiplier(),
			];
		}
		foreach ($defendingMatchups as $matchup) {
			$attackingType = $types[$matchup->getAttackingTypeId()->value()];
			$this->matchups['whenDefending'][] = [
				'type' => $attackingType,
				'multiplier' => $matchup->getMultiplier(),
			];
		}

		// Get stat name abbreviations.
		$this->stats = $this->statNameModel->getByGeneration($generationId, $languageId);

		// Get Pokémon with this type.
		$this->pokemon = $this->dexPokemonRepository->getByType(
			$generationId,
			$type->getId(),
			$languageId
		);

		// Get moves with this type.
		$this->moves = $this->dexMoveRepository->getByType(
			$generationId,
			$type->getId(),
			$languageId
		);
	}

	/**
	 * Get the generation model.
	 *
	 * @return GenerationModel
	 */
	public function getGenerationModel() : GenerationModel
	{
		return $this->generationModel;
	}

	/**
	 * Get the type.
	 *
	 * @return array
	 */
	public function getType() : array
	{
		return $this->type;
	}

	/**
	 * Get the matchups.
	 *
	 * @return array
	 */
	public function getMatchups() : array
	{
		return $this->matchups;
	}

	/**
	 * Get the stats and their names.
	 *
	 * @return array
	 */
	public function getStats() : array
	{
		return $this->stats;
	}

	/**
	 * Get the Pokémon.
	 *
	 * @return DexPokemon[]
	 */
	public function getPokemon() : array
	{
		return $this->pokemon;
	}

	/**
	 * Get the moves.
	 *
	 * @return DexMove[]
	 */
	public function getMoves() : array
	{
		return $this->moves;
	}
}
