<?php

declare(strict_types=1);

namespace kodorvan\constructor\controllers;

// Files of the project
use kodorvan\constructor\views\templater,
	kodorvan\constructor\models\core as models;

// Library for languages support
use mirzaev\languages\language;

// Framework for PHP
use mirzaev\minimal\core as minimal,
	mirzaev\minimal\controller,
	mirzaev\minimal\http\response,
	mirzaev\minimal\http\enumerations\status;

/**
 * Controllers core
 *
 * @package kodorvan\constructor\controllers
 *
 * @param language $language Language
 * @param response $response Response
 * @param array $errors Registry of errors
 *
 * @method void __construct(minimal $minimal) Constructor
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
class core extends controller
{
	/**
	 * Language
	 *
	 * @var language $language Language
	 */
	protected language $language = language::en;

	/**
	 * Response
	 *
	 * @see https://wiki.php.net/rfc/property-hooks (find a table about backed and virtual hooks)
	 *
	 * @var response $response Response
	 */
	protected response $response {
		// Read
		get => $this->response ??= $this->request->response();
	}

	/**
	 * Errors
	 *
	 * @var array $errors Registry of errors
	 */
	protected array $errors = [
		'system' => []
	];

	/**
	 * Constructor
	 *
	 * @param minimal $core Instance of the MINIMAL
	 * @param bool $initialize Initialize a controller?
	 *
	 * @return void
	 */
	public function __construct(minimal $core)
	{
		// Blocking requests from CloudFlare (better to write this blocking into nginx config file)
		if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] === 'nginx-ssl early hints') return status::bruh->label;

		// Initializing the view template engine instance
		$this->view = new templater();

		// For the extends system
		parent::__construct(core: $core);
	}
}
