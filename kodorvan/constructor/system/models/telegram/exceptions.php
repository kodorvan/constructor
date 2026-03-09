<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\localization,
	kodorvan\constructor\models\settings as model,
	kodorvan\constructor\models\telegram\processes\language\select as process_language_select;

// Library for languages support
use mirzaev\languages\language;

// The library for escaping all markdown symbols
use function mirzaev\unmarkdown;

// Framework for Telegram
use SergiX44\Nutgram\Nutgram as telegram,
	SergiX44\Nutgram\Telegram\Exceptions\TelegramException as telegram_exception,
	SergiX44\Nutgram\Exception\ApiException as telegram_api_exception;

// Built-in libraries
use Error as error;

/**
 * Telegram exceptions
 *
 * @package kodorvan\constructor\models\telegram
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 *
 * @deprecated
 */
final class exceptions extends telegram_api_exception
{
	public static ?string $pattern = '.*';

	public function __invoke(telegram $robot, telegram_exception $exception)
	{
		// override this method to change the default behaviour:
		$robot->sendMessage('robot zdox');
		throw new static($exception->getMessage(), $exception->getCode(), $exception);
	}
}
