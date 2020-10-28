<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Languages;

final class Language
{
	public function __construct(
		private LanguageId $id,
		private string $identifier,
		private string $locale,
		private string $dateFormat,
	) {}

	/**
	 * Get the language's id.
	 *
	 * @return LanguageId
	 */
	public function getId() : LanguageId
	{
		return $this->id;
	}

	/**
	 * Get the language's identifier.
	 *
	 * @return string
	 */
	public function getIdentifier() : string
	{
		return $this->identifier;
	}

	/**
	 * Get the language's locale.
	 *
	 * @return string
	 */
	public function getLocale() : string
	{
		return $this->locale;
	}

	/**
	 * Get the language's date format.
	 *
	 * @return string
	 */
	public function getDateFormat() : string
	{
		return $this->dateFormat;
	}
}
