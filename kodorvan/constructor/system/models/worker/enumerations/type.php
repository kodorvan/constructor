<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\worker\enumerations;

// The library for languages support
use mirzaev\languages\language;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Type
 *
 * @package kodorvan\neurobot\models\worker\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum type
{
	case programmer;
	case designer;
	case booster;

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
			static::programmer => match ($language) {
				language::en => 'Programmer',
				language::ru => 'Программист'
			},
			static::designer => match ($language) {
				language::en => 'Designer',
				language::ru => 'Дизайнер'
			},
			static::booster => match ($language) {
				language::en => 'Booster',
				language::ru => 'Бустер'
			}
		};
	}
}
