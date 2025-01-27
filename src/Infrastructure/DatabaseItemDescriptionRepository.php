<?php
declare(strict_types=1);

namespace Jp\Dex\Infrastructure;

use Jp\Dex\Domain\Items\ItemDescription;
use Jp\Dex\Domain\Items\ItemDescriptionRepositoryInterface;
use Jp\Dex\Domain\Items\ItemId;
use Jp\Dex\Domain\Languages\LanguageId;
use Jp\Dex\Domain\Versions\GenerationId;
use PDO;

final class DatabaseItemDescriptionRepository implements ItemDescriptionRepositoryInterface
{
	public function __construct(
		private PDO $db,
	) {}

	/**
	 * Get an item description by generation, language, and item.
	 */
	public function getByGenerationAndLanguageAndItem(
		GenerationId $generationId,
		LanguageId $languageId,
		ItemId $itemId
	) : ItemDescription {
		$stmt = $this->db->prepare(
			'SELECT
				`description`
			FROM `item_descriptions`
			WHERE `generation_id` = :generation_id
				AND `language_id` = :language_id
				AND `item_id` = :item_id
			LIMIT 1'
		);
		$stmt->bindValue(':generation_id', $generationId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':language_id', $languageId->value(), PDO::PARAM_INT);
		$stmt->bindValue(':item_id', $itemId->value(), PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return new ItemDescription($generationId, $languageId, $itemId, '');
		}

		$itemDescription = new ItemDescription(
			$generationId,
			$languageId,
			$itemId,
			$result['description']
		);

		return $itemDescription;
	}
}
