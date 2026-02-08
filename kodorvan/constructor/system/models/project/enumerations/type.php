<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

// Files of the project
use kodorvan\constructor\models\project\enumerations\purpose;

// The library for languages support
use mirzaev\languages\language;

// The library for currencies support
use mirzaev\currencies\currency;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Type
 *
 * @package kodorvan\neurobot\models\project\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum type
{
	case chat_robot;
	case parser;
	case calculator;
	case crm;
		/* case marketplace; */
	case site;
	case program;

	case complex;

	/**
	 * Label
	 *
	 * @param language $language The language
	 *
	 * @return string The project type label
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
			static::calculator => match ($language) {
				language::en => 'Calculator',
				language::ru => 'Калькулятор'
			},
			static::crm => match ($language) {
				default => 'CRM'
			},
			/* static::marketplace => match ($language) {
				language::en => 'Marketplace',
				language::ru => 'Маркетплейс'
			}, */
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
			static::chat_robot => 2,
			static::parser => 1,
			static::calculator => 2,
			static::crm => 1,
			/* static::marketplace => 4, */
			static::site => 1,
			static::program => 1,
			static::complex => 2,
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
		// Exit (success)
		return match ($this) {
			static::chat_robot => [
				purpose::funnel,
				purpose::contact,
				purpose::neural_network,
				purpose::game,
				purpose::gallery,
				purpose::crm,
				purpose::landing,
				purpose::marketplace,
				purpose::events,
				purpose::charity
			],
			static::parser => [
				purpose::search
			],
			static::calculator => [
				purpose::calculate
			],
			static::crm => [
				purpose::workers,
				purpose::tools,
				purpose::objects,
				purpose::events
			],
			static::site => [
				purpose::funnel,
				purpose::contact,
				purpose::neural_network,
				purpose::gallery,
				purpose::crm,
				purpose::landing,
				purpose::marketplace,
				purpose::workers,
				purpose::tools,
				purpose::objects,
				purpose::events,
				purpose::charity
			],
			static::program => [
				purpose::neural_network,
				purpose::crm,
				purpose::marketplace,
				purpose::workers,
				purpose::tools,
				purpose::objects,
				purpose::events,
				purpose::charity
			],
			default => []
		};
	}

	/**
	 * Cost
	 *
	 * @return int|float The minimal cost of the project development
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
			static::calculator => match ($currency) {
				currency::usd => 40,
				currency::rub => 4000
			},
			static::crm => match ($currency) {
				currency::usd => 100,
				currency::rub => 8000
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
}
