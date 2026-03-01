<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\worker\enumerations;

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
 *
 * @deprecated
 */
enum type
{
	case programmer;
	case designer;
}
