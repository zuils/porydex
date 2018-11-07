<?php
declare(strict_types=1);

namespace Jp\Dex\Infrastructure;

use Jp\Dex\Domain\Versions\Generation;
use Jp\Dex\Domain\Versions\GenerationId;
use Jp\Dex\Domain\Versions\GenerationNotFoundException;
use Jp\Dex\Domain\Versions\GenerationRepositoryInterface;
use PDO;

class DatabaseGenerationRepository implements GenerationRepositoryInterface
{
	/** @var PDO $db */
	private $db;

	/**
	 * Constructor.
	 *
	 * @param PDO $db
	 */
	public function __construct(PDO $db)
	{
		$this->db = $db;
	}

	/**
	 * Get a generation by its id.
	 *
	 * @param GenerationId $generationId
	 *
	 * @throws GenerationNotFoundException if no generation exists with this id.
	 *
	 * @return Generation
	 */
	public function getById(GenerationId $generationId) : Generation
	{
		$stmt = $this->db->prepare(
			'SELECT
				`identifier`
			FROM `generations`
			WHERE `id` = :generation_id
			LIMIT 1'
		);
		$stmt->bindValue(':generation_id', $generationId->value(), PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			throw new GenerationNotFoundException(
				'No generation exists with id ' . $generationId->value() . '.'
			);
		}

		$generation = new Generation(
			$generationId,
			$result['identifier']
		);

		return $generation;
	}

	/**
	 * Get a generation by its identifier
	 *
	 * @param string $identifier
	 *
	 * @throws GenerationNotFoundException if no generation exists with this
	 *     identifier.
	 *
	 * @return Generation
	 */
	public function getByIdentifier(string $identifier) : Generation
	{
		$stmt = $this->db->prepare(
			'SELECT
				`id`
			FROM `generations`
			WHERE `identifier` = :identifier
			LIMIT 1'
		);
		$stmt->bindValue(':identifier', $identifier, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			throw new GenerationNotFoundException(
				"No generation exists with identifier $identifier."
			);
		}

		$generation = new Generation(
			new GenerationId($result['id']),
			$identifier
		);

		return $generation;
	}
}