<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

// Built-in libraries
use InvalidArgumentException as exception_argument,
	DomainException as exception_domain;

/**
 * Status
 *
 * @package kodorvan\neurobot\models\project\enumerations
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
enum status
{
	case creating;
	case calculated;
	case requested;

	case invoiced;

	case developing;
	case developed;
	case launched;
}
