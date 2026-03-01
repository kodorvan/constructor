<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

// The library for languages support
use mirzaev\languages\language;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Integration
 *
 * @package kodorvan\neurobot\models\project\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum integration
{
	case one_c;
	case bitrix24;
	case moy_sklad;
	case telegram;
	case mail;
	case excel;

	/**
	 * Label
	 *
	 * @param language $language The language
	 *
	 * @return string The project form label
	 */
	public function label(language $language = LANGUAGE_DEFAULT): string
	{
		// Exit (success)
		return match ($this) {
			static::one_c => '1C',
			static::bitrix24 => match ($language) {
				language::en => 'Bitrix 24',
				language::ru => 'Битрикс 24'
			},
			static::moy_sklad => match ($language) {
				language::en => 'Moy Sklad',
				language::ru => 'Мой Склад'
			},
			static::telegram => match ($language) {
				language::en => 'Telegram',
				language::ru => 'Телеграм'
			},
			static::mail => match ($language) {
				language::en => 'Mail',
				language::ru => 'Почта'
			},
			static::excel => 'Excel'
		};
	}

	/**
	 * Length
	 *
	 * @return int Amount of buttons cells length
	 */
	public function length(): int
	{
		// Exit (success)
		return match ($this) {
			static::one_c => 1,
			static::bitrix24 => 2,
			static::moy_sklad => 2,
			static::telegram => 2,
			static::mail => 1,
			static::excel => 1,
		};
	}

	/**
	 * Coefficient
	 *
	 * @return int|float Coefficient to the project development hours
	 */
	public function coefficient(): int|float
	{
		// Exit (success)
		return match ($this) {
			static::one_c => 3,
			static::bitrix24 => 3.5,
			static::moy_sklad => 3,
			static::telegram => 2,
			static::mail => 1.2,
			static::excel => 1.5,
			default => 2
		};
	}
}
