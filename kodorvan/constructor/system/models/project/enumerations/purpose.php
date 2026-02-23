<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

// The library for languages support
use mirzaev\languages\language;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Purpose
 *
 * @package kodorvan\neurobot\models\project\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum purpose
{
	case funnel;
	case contact;
	case neural_network;
	case gallery;
	case crm;
	case landing;
	case marketplace;
	case charity;
	case search;
	case calculate;
	case logic;
	case game;

	case workers;
	case tools;
	case objects;
	case events;

	case special;

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
			static::funnel => match ($language) {
				language::en => 'Funnel',
				language::ru => 'Воронка'
			},
			static::contact => match ($language) {
				language::en => 'Contact',
				language::ru => 'Контакты'
			},
			static::neural_network => match ($language) {
				language::en => 'Neural network',
				language::ru => 'Нейросеть'
			},
			static::game => match ($language) {
				language::en => 'Game',
				language::ru => 'Игра'
			},
			static::gallery => match ($language) {
				language::en => 'Gallery',
				language::ru => 'Галерея'
			},
			static::crm => match ($language) {
				default => 'CRM'
			},
			static::landing => match ($language) {
				language::en => 'Landing',
				language::ru => 'Лендинг'
			},
			static::marketplace => match ($language) {
				language::en => 'Marketplace',
				language::ru => 'Маркетплейс'
			},
			static::charity => match ($language) {
				language::en => 'Charity',
				language::ru => 'Благотворительность'
			},
			static::search => match ($language) {
				language::en => 'Search',
				language::ru => 'Поиск'
			},
			static::calculate => match ($language) {
				language::en => 'Calculate',
				language::ru => 'Расчёты'
			},
			static::logic => match ($language) {
				language::en => 'Logic',
				language::ru => 'Логика'
			},
			static::game => match ($language) {
				language::en => 'Game',
				language::ru => 'Игра'
			},
			static::workers => match ($language) {
				language::en => 'Workes',
				language::ru => 'Рабочие'
			},
			static::tools => match ($language) {
				language::en => 'Tools',
				language::ru => 'Инструменты'
			},
			static::objects => match ($language) {
				language::en => 'Objects',
				language::ru => 'Предметы'
			},
			static::events => match ($language) {
				language::en => 'Events',
				language::ru => 'События'
			},
			static::special => match ($language) {
				language::en => 'Special',
				language::ru => 'Особенный'
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
			static::funnel => 2,
			static::contact => 2,
			static::neural_network => 3,
			static::game => 1,
			static::gallery => 1,
			static::crm => 1,
			static::landing => 1,
			static::marketplace => 2,
			static::charity => 2,
			static::search => 2,
			static::calculate => 2,
			static::logic => 1,
			static::tools => 1,
			static::workers => 1,
			static::objects => 1,
			static::events => 1,
			static::special => 4,
			default => 1
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
			static::funnel => 1.4,
			static::contact => 1.1,
			static::neural_network => 2,
			static::game => 3,
			static::gallery => 1,
			static::crm => 3,
			static::landing => 1.2,
			static::marketplace => 2,
			static::charity => 0.8,
			static::search => 1,
			static::calculate => 1.1,
			static::logic => 1,
			static::tools => 1,
			static::workers => 1.2,
			static::objects => 1,
			static::events => 1.5,
			static::special => 2,
			default => 1
		};
	}
}
