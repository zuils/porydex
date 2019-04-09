<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Middleware;

use DateTime;
use Jp\Dex\Application\CookieNames;
use Jp\Dex\Domain\Formats\FormatId;
use Jp\Dex\Domain\Formats\FormatRepositoryInterface;
use Jp\Dex\Domain\Stats\Usage\UsageQueriesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CurrentStatsMiddleware implements MiddlewareInterface
{
	/** @var FormatRepositoryInterface $formatRepository */
	private $formatRepository;

	/** @var UsageQueriesInterface $usageQueries */
	private $usageQueries;

	/** @var int $DEFAULT_FORMAT_ID */
	private const DEFAULT_FORMAT_ID = FormatId::GEN_7_OU;

	/** @var int $DEFAULT_RATING */
	private const DEFAULT_RATING = 1695;

	/**
	 * Constructor.
	 *
	 * @param FormatRepositoryInterface $formatRepository
	 * @param UsageQueriesInterface $usageQueries
	 */
	public function __construct(
		FormatRepositoryInterface $formatRepository,
		UsageQueriesInterface $usageQueries
	) {
		$this->formatRepository = $formatRepository;
		$this->usageQueries = $usageQueries;
	}

	/**
	 * Set request attributes for the stats usage page so it shows the latest
	 * data for the user's default format.
	 *
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 *
	 * @return ResponseInterface
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	) : ResponseInterface {
		// Get the format and rating from the user's cookies.
		$cookies = $request->getCookieParams();
		$formatIdentifier = $cookies[CookieNames::FORMAT] ?? '';
		$rating = $cookies[CookieNames::RATING] ?? '';

		if ($formatIdentifier) {
			$format = $this->formatRepository->getByIdentifier($formatIdentifier);
		} else {
			// If the user doesn't have a format cookie, use default format.
			$formatId = new FormatId(self::DEFAULT_FORMAT_ID);
			$format = $this->formatRepository->getById($formatId);
		}

		// If the user doesn't have a rating cookie, use default rating.
		if ($rating === '') {
			$rating = (string) self::DEFAULT_RATING;
		}

		// Get the latest month of data for the format.
		$month = $this->usageQueries->getNewest($format->getId());
		if ($month === null) {
			// This format has no data ever, so it doesn't matter what month we use.
			$month = new DateTime();
		}

		// Set the attributes.
		$request = $request->withAttribute('month', $month->format('Y-m'));
		$request = $request->withAttribute('formatIdentifier', $format->getIdentifier());
		$request = $request->withAttribute('rating', $rating);

		return $handler->handle($request);
	}
}
