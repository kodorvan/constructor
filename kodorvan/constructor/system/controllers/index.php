<?php

declare(strict_types=1);

namespace kodorvan\constructor\controllers;

// Files of the project
use kodorvan\constructor\controllers\core;

// Framework for PHP
use mirzaev\minimal\http\enumerations\content,
  mirzaev\minimal\http\enumerations\status;

/**
 * Index
 *
 * @package kodorvan\constructor\controllers
 *
 * @param array $errors Registry of errors
 *
 * @method null index() Main page
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class index extends core
{
	/**
	 * Errors
	 *
	 * @var array $errors Registry of errors
	 */
	protected array $errors = [
		'system' => []
	];

	/**
	 * Main page
	 *
	 * @return null
	 */
	public function index(): null
	{
		if (str_contains($this->request->headers['accept'] ?? '', content::html->value)) {
			// Request for HTML response

			// Render page
			$page = $this->view->render('index.html');

			// Sending response
			$this->response
				->start()
				->clean()
				->sse()
				->write($page)
				->validate($this->request)
				?->body()
				->end();

			// Deinitializing rendered page
			unset($page);

			// Exit (success)
			return null;
		}

		// Exit (fail)
		return null;
	}
}
