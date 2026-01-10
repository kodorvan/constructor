<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\project\enumerations;

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
	case telegram_voronka;
	case parser;
	case calculator;
	case crm;
	case marketplace;
	case site;
	case program;

	case special;
}
