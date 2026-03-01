<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

// Files of the project
use kodorvan\constructor\models\project\enumerations\purpose,
	kodorvan\constructor\models\project\enumerations\integration;

// The library for languages support
use mirzaev\languages\language;

// The library for currencies support
use mirzaev\currencies\currency;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Architecture
 *
 * @package kodorvan\neurobot\models\project\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum architecture
{
	case chat_robot;
	case parser;
	case script;
	case site;
	case program;

	case complex;

	/**
	 * Label
	 *
	 * @param language $language The language
	 *
	 * @return string The project architecture label
	 */
	public function label(language $language = LANGUAGE_DEFAULT): string
	{
		// Exit (success)
		return match ($this) {
			static::chat_robot => match ($language) {
				language::en => 'Chat-robot',
				language::ru => 'Чат-робот'
			},
			static::parser => match ($language) {
				language::en => 'Parser',
				language::ru => 'Парсер'
			},
			static::script => match ($language) {
				language::en => 'Script',
				language::ru => 'Скрипт'
			},
			static::site => match ($language) {
				language::en => 'Site',
				language::ru => 'Сайт'
			},
			static::program => match ($language) {
				language::en => 'Program',
				language::ru => 'Программа'
			},
			static::complex => match ($language) {
				language::en => 'Non-standart',
				language::ru => 'Нестандартный'
			}
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
			static::chat_robot => 1,
			static::parser => 1,
			static::script => 1,
			static::site => 1,
			static::program => 1,
			static::complex => 4,
			default => 1
		};
	}

	/**
	 * Purposes
	 *
	 * @return array Purposes
	 */
	public function purposes(): array
	{
		// Initializing purposes
		$purposes = purpose::cases();

		// Deleting the special purpose
		$indexes = array_keys($purposes, purpose::special);
		foreach ($indexes as $index) unset($purposes[$index]);

		// Exit (success)
		return match ($this) {
			static::chat_robot => [
				purpose::funnel,
				purpose::contact,
				purpose::neural_network,
				purpose::game,
				purpose::gallery,
				purpose::crm,
				purpose::calculate,
				purpose::landing,
				purpose::marketplace,
			],
			static::parser => [
				purpose::search
			],
			static::script => [
				purpose::logic
			],
			static::site => [
				purpose::funnel,
				purpose::contact,
				purpose::neural_network,
				purpose::gallery,
				purpose::crm,
				purpose::calculate,
				purpose::landing,
				purpose::marketplace,
			],
			static::program => [
				purpose::neural_network,
				purpose::crm,
				purpose::calculate,
				purpose::marketplace,
			],
			default => []
		};
	}

	/**
	 * Integrations
	 *
	 * @return array Integrations
	 */
	/* public function integrations(): array
	{
		// Exit (success)
		return match ($this) {
			static::chat_robot => [
				integration::one_c,
				integration::moy_sklad
			],
			static::parser => [
				integration::one_c,
				integration::moy_sklad
			],
			static::script => [],
			static::crm => [
				integration::one_c,
				integration::moy_sklad
			],
			static::site => [
				integration::one_c,
				integration::moy_sklad
			],
			static::program => [
				integration::one_c,
				integration::moy_sklad
			],
			static::complex => [
				integration::one_c,
				integration::moy_sklad
			],
			default => []
		};
	} */

	/**
	 * Cost
	 *
	 * @return int|float The minimal cost of the project development
	 *
	 * @deprecated
	 */
	public function cost(currency $currency = CURRENCY_DEFAULT): int|float
	{
		// Exit (success)
		return match ($this) {
			static::chat_robot => match ($currency) {
				currency::usd => 20,
				currency::rub => 2000
			},
			static::parser => match ($currency) {
				currency::usd => 35,
				currency::rub => 3500
			},
			static::script => match ($currency) {
				currency::usd => 10,
				currency::rub => 1000
			},
			static::site => match ($currency) {
				currency::usd => 50,
				currency::rub => 5000
			},
			static::program => match ($currency) {
				currency::usd => 70,
				currency::rub => 6000
			},
			static::complex => match ($currency) {
				currency::usd => 100,
				currency::rub => 10000
			}
		};
	}

	/**
	 * Coefficient
	 *
	 * @return int The project development hours
	 */
	public function coefficient(): int|float
	{
		// Exit (success)
		return (int) match ($this) {
			static::chat_robot => 3,
			static::parser => 2,
			static::script => 1,
			static::site => 3,
			static::program => 4,
			static::complex => 5,
			default => 5
		};
	}
}
