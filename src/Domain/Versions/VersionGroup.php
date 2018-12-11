<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Versions;

class VersionGroup
{
	/** @var VersionGroupId $id */
	private $id;

	/** @var string $identifier */
	private $identifier;

	/** @var GenerationId $generationId */
	private $generationId;

	/**
	 * Constructor.
	 *
	 * @param VersionGroupId $versionGroupId
	 * @param string $identifier
	 * @param GenerationId $generationId
	 */
	public function __construct(
		VersionGroupId $versionGroupId,
		string $identifier,
		GenerationId $generationId
	) {
		$this->id = $versionGroupId;
		$this->identifier = $identifier;
		$this->generationId = $generationId;
	}

	/**
	 * Get the version group's id.
	 *
	 * @return VersionGroupId
	 */
	public function getId() : VersionGroupId
	{
		return $this->id;
	}

	/**
	 * Get the version group's identifier.
	 *
	 * @return string
	 */
	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	/**
	 * Get the version group's generation id.
	 *
	 * @return GenerationId
	 */
	public function getGenerationId() : GenerationId
	{
		return $this->generationId;
	}
}