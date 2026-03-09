<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\deal\enumerations;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Direction
 *
 * @package kodorvan\neurobot\models\deal\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum direction: int
{
	case inbound = 0;
	case outbound = 1;
}
