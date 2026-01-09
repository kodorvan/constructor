<?php

declare(strict_types=1);

namespace kodorvan\constructor\views;

// Library for languages support
use mirzaev\languages\language;

// Framework for PHP
use mirzaev\minimal\controller;

// Templater of views
use Twig\Loader\FilesystemLoader,
	Twig\Environment as twig,
	Twig\Extra\Intl\IntlExtension as intl,
	Twig\TwigFilter,
	Twig\TwigFunction;

// Built-in libraries
use ArrayAccess as array_access,
	Error as error;

/**
 * Templater
 *
 * @package kodorvan\constructor\views
 *
 * @param twig $twig Instance of the twig templater
 * @param array $variables Registry of view global variables
 *
 * @method void __construct() Constructor
 * @method string|null render(string $file, ?array $variables) Render the HTML-document
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class templater extends controller implements array_access
{
	/**
	 * Twig
	 * 
	 * @var twig $twig Instance of the twig templater
	 */
	readonly public twig $twig;

	/**
	 * Variables
	 * 
	 * @var array $variables Registry of view global variables
	 */
	public array $variables = [];

	/**
	 * Constructor of an instance
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Initializing the Twig instance
		$this->twig = new twig(new FilesystemLoader(VIEWS));

		// Initializing global variables
		$this->twig->addGlobal('theme', 'default');
		$this->twig->addGlobal('server', $_SERVER);
		$this->twig->addGlobal('cookies', $_COOKIE);
		$this->twig->addGlobal('language', $language = $session?->buffer['language'] ?? language::en);
	}

	/**
	 * Render 
	 *
	 * Render the HTML-document
	 *
	 * @param string $file Related path to a HTML-document
	 * @param array $variables Registry of variables to push into registry of global variables
	 *
	 * @return ?string HTML-document
	 */
	public function render(string $file, array $variables = []): ?string
	{
		// Generation and exit (success)
		return $this->twig->render('themes' . DIRECTORY_SEPARATOR . $this->twig->getGlobals()['theme'] . DIRECTORY_SEPARATOR . $file, $variables + $this->variables);

	}

	/**
	 * Write
	 *
	 * Write the variable into the registry of the view global variables
	 *
	 * @param string $name Name of the variable
	 * @param mixed $value Value of the variable
	 *
	 * @return void
	 */
	public function __set(string $name, mixed $value = null): void
	{
		// Write the variable and exit (success)
		$this->variables[$name] = $value;
	}

	/**
	 * Read
	 *
	 * Read the variable from the registry of the view global variables
	 *
	 * @param string $name Name of the variable
	 *
	 * @return mixed Content of the variable, if they are found
	 */
	public function __get(string $name): mixed
	{
    // Read the variable and exit (success)
		return $this->variables[$name];
	}

	/**
	 * Delete
	 *
	 * Delete the variable from the registry of the view global variables
	 *
	 * @param string $name Name of the variable 
	 *
	 * @return void
	 */
	public function __unset(string $name): void
	{
	 // Delete the variable and exit (success)
		unset($this->variables[$name]);
	}

	/**
	 * Check of initialization
	 *
	 * Check of initialization in the registry of the view global variables
	 *
	 * @param string $name Name of the variable
	 *
	 * @return bool The variable is initialized?
	 */
	public function __isset(string $name): bool
	{
		// Check of initialization of the variable and exit (success)
		return isset($this->variables[$name]);
	}

	/**
	 * Write
	 *
	 * Write the variable into the registry of the view global variables
	 *
	 * @param mixed $name Name of an offset of the variable
	 * @param mixed $value Value of the variable
	 *
	 * @return void
	 */
	public function offsetSet(mixed $name, mixed $value): void
	{
		// Write the variable and exit (success)
		$this->variables[$name] = $value;
	}

	/**
	 * Read
	 *
	 * Read the variable from the registry of the view global variables
	 *
	 * @param mixed $name Name of the variable
	 *
	 * @return mixed Content of the variable, if they are found
	 */
	public function offsetGet(mixed $name): mixed
	{
    // Read the variable and exit (success)
		return $this->variables[$name];
	}

	/**
	 * Delete
	 *
	 * Delete the variable from the registry of the view global variables
	 *
	 * @param mixed $name Name of the variable 
	 *
	 * @return void
	 */
	public function offsetUnset(mixed $name): void
	{
	 // Delete the variable and exit (success)
		unset($this->variables[$name]);
	}

	/**
	 * Check of initialization
	 *
	 * Check of initialization in the registry of the view global variables
	 *
	 * @param mixed $name Name of the variable
	 *
	 * @return bool The variable is initialized?
	 */
	public function offsetExists(mixed $name): bool
	{
		// Check of initialization of the variable and exit (success)
		return isset($this->variables[$name]);
	}
}

