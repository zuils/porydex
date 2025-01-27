<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Controllers;

use Jp\Dex\Application\Models\BreedingChains\BreedingChainsModel;
use Jp\Dex\Domain\Languages\LanguageId;
use Psr\Http\Message\ServerRequestInterface;

final class BreedingChainsController
{
	public function __construct(
		private BaseController $baseController,
		private BreedingChainsModel $breedingChainsModel,
	) {}

	/**
	 * Show the breeding chains page.
	 */
	public function index(ServerRequestInterface $request) : void
	{
		$this->baseController->setBaseVariables($request);

		$generationIdentifier = $request->getAttribute('generationIdentifier');
		$pokemonIdentifier = $request->getAttribute('pokemonIdentifier');
		$moveIdentifier = $request->getAttribute('moveIdentifier');
		$versionGroupIdentifier = $request->getAttribute('versionGroupIdentifier');
		$languageId = new LanguageId((int) $request->getAttribute('languageId'));

		$this->breedingChainsModel->setData(
			$generationIdentifier,
			$pokemonIdentifier,
			$moveIdentifier,
			$versionGroupIdentifier,
			$languageId
		);
	}
}
