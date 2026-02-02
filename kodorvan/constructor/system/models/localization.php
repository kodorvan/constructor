<?php

declare(strict_types=1);

namespace kodorvan\constructor\models;

// Files of the project
use kodorvan\constructor\models\core;

// Baza database
use mirzaev\baza\database,
	mirzaev\baza\column,
	mirzaev\baza\record,
	mirzaev\baza\enumerations\encoding,
	mirzaev\baza\enumerations\type;

// The library for languages support
use mirzaev\languages\language;

// Built-in libraries
use ArrayAccess as array_access,
	Exception as exception,
	RuntimeException as exception_runtime,
	LogicException as exception_logic;

/**
 * Localization
 *
 * @package kodorvan\constructor\models
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class localization extends core implements array_access
{
	/**
	 * File
	 *
	 * @var string $file Path to the localization file
	 */
	protected string $file;

	/**
	 * Language
	 *
	 * @var language $language The localization language
	 */
	public readonly language $language;

	/**
	 * Registry
	 *
	 * @var array $registry The localization records
	 */
	protected array $registry;

	/**
	 * Constructor
	 *
	 * @method language $language The language
	 *
	 * @throws exception_runtime If failed to initialize the localization file
	 *
	 * @return void
	 */
	public function __construct(language $language)
	{
		// Initializing the localization language
		$this->language = $language;

		// Initializing the path to the localization file
		$this->file = LOCALIZATIONS . DIRECTORY_SEPARATOR . strtolower($this->language->label()) . '.php';

		if (file_exists($this->file) && is_readable($this->file)) {
			// Found the localization file

			// Initializing the localization registry
			$this->registry = require($this->file);
		} else {
			// Not found the localization file

			// Exit (fail)
			throw new exception_runtime('Failed to initialize the localization file');
		}
	}

	/**
	 * Write as array
	 *
	 * @param mixed $offset The record name
	 * @param mixed $value The record value
	 *
	 * @return void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_null($offset)) {
			// Not received name of the localization record 

			// Exit (fail)
			throw new exception_logic('Failed to initialized the localization record name');
		} else {
			// Received name of the localization record

			// Writing the record into into the registry
			$this->registry[$offset] = $value;
		}
	}

	/**
	 * Read as from array
	 *
	 * @param mixed $offset The record name
	 *
	 * @return mixed The record value
	 */
	public function offsetGet(mixed $offset): mixed
	{
		// Reading the record from the registry and exit (success)
		return $this->registry[$offset] ?? null;
	}

	/**
	 * Check for initializing as array
	 *
	 * @param mixed $offset The record name
	 *
	 * @return bool Is the record initialized?
	 */
	public function offsetExists(mixed $offset): bool
	{
		// Checking the record existance in the registry and exit (success)
		return isset($this->registry[$offset]);
	}

	/**
	 * Delete as from array
	 *
	 * @param mixed $offset The record name
	 *
	 * @return void
	 */
	public function offsetUnset(mixed $offset): void
	{
		// Deleting the record from the registry and exit (succes)
		unset($this->registry[$offset]);
	}

	/**
	 * Write
	 *
	 * @param string $name The record name
	 * @param mixed $value The record value
	 *
	 * @return void
	 */
	public function __set(string $name, mixed $value = null): void
	{
		// Writing the record into the registry
		$this->registry[$name] = $value;
	}

	/**
	 * Read
	 *
	 * @param string $name The record name
	 *
	 * @return mixed The record value
	 */
	public function __get(string $name): mixed
	{
		// Reading the record from the registry and exit (success)
		return $this->registry[$name];
	}

	/**
	 * Check for initializing
	 *
	 * @param string $name The record name
	 *
	 * @return bool Is the record initialized?
	 */
	public function __isset(string $name): bool
	{
		// Check for initializing of the property and exit (success)
		return isset($this->registry[$name]);
	}

	/**
	 * Delete
	 *
	 * @param string $name The record name
	 *
	 * @return void
	 */
	public function __unset(string $name): void
	{
		// Deleting the record from the registry and exit (success)
		unset($this->registry[$name]);
	}
}
