<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Framework for PHP
use mirzaev\minimal\model,
	mirzaev\minimal\http\enumerations\status;

// Built-in libraries
use exception;

/**
 * Models core
 *
 * @package kodorvan\constructor\models
 *
 * @method void __construct() Constructor
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class core extends model
{
	/**
	 * File
	 *
	 * @var string database Path to the database file
	 */
	protected string $file = DATABASES . DIRECTORY_SEPARATOR . 'example.baza';

	/**
	 * Constructor
	 *
	 * Initialize the database
	 *
	 * @return void
	 */
	public function __construct()
	{
	}
}

