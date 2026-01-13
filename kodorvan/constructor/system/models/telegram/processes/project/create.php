<?php

declare(strict_types=1);

namespace kodorvan\constructor\models\telegram\processes\project;

// Files of the project
use kodorvan\constructor\models\core,
	kodorvan\constructor\models\account,
	kodorvan\constructor\models\settings,
	kodorvan\constructor\models\project,
	kodorvan\constructor\models\project\enumerations\status as project_status;

// Library for projects support
use mirzaev\projects\project;

// Baza database
use mirzaev\baza\record;

// Framework for Telegram
use Zanzara\Context as context,
	Zanzara\Telegram\Type\Message as message;

/**
 * Telegram project create 
 *
 * @package kodorvan\constructor\models\telegram\processes\project
 *
 * @license http://www.wtfpl.net/ Do What The Fuck You Want To Public License
 * @author Arsen Mirzaev Tatyano-Muradovich <arsen@mirzaev.sexy>
 */
final class create extends core
{
	/**
	 * Start
	 *
	 * Starting the account localization creating process
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function start(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Searching for the project
					$project = $account->projects(status: project_status::creation, amount: 1)[0] ?? null;

					if ($project instanceof project) {
						// Found the project

						// Sending the project creation menu
						static::menu($context);
					} else {
						// Not found the project

						// Initializing buffer of languages
						$languages = language::cases();

						// Deleting the actual language from buffer of languages
						unset($languages[array_search($language, $languages, strict: true)]);

						// Sorting buffer of languages by the actual language
						$languages = [$language, ...$languages];

						// Initializing the account model
						$model_account = new account;

						// Initializing the account localizations
						$existed = $model_account->localization->database->read(
							filter: fn(record $localization) => $localization->account === $account->identifier,
							amount: ACCOUNT_LOCALIZATION_CREATE_ACCOUNT_LOCALIZATIONS_AMOUNT
						);

						if (count($existed) !== count(language::cases())) {
							// Not all languages in the registry have localizations created (expected)

							// Declaring the buffer of languages to exclude
							$exclude = [];

							// Initializing languages to exclude
							foreach ($existed as $record) $exclude[] = $record->language;

							if (!empty($exclude)) {
								// Initialized languages to exclude 

								// Deleting excluded languages
								$languages = array_filter($languages, fn(language $language) => array_search($language->name, $exclude, strict: true) === false);
							}

							// Initializing the account localization create buffer
							$process = [
								'language' => array_values($languages)[0],
								'name' => null,
							];

							// Writing to the telegram user buffer
							$context->setUserDataItem(static::PROCESS, $process)
								->then(function () use ($context, $account, $localization) {
									// Writed to the telegram user buffer

									// Sending the message
									$context->sendMessage('📂 *' . $localization['account_localization_create_started'] . '*')
										->then(function (message $message) use ($context, $account, $localization) {
											// Sended the message

											// Sending the account localization create menu
											static::menu($context);
										});
								});
						} else {
							// All languages in the registry have localizations created (expected)

							// Sending the message
							$context->sendMessage('⚠️ *' . $localization['account_localization_create_every_language_created'] . '*')
								->then(function (message $message) use ($context) {
									// Sended the message

									// Ending the conversation process
									$context->endConversation();
								});
						}
					}
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
							// Ending the conversation process
							$context->endConversation();
						});
				}
			} else {
				// Not initialized language

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize language*')
					->then(function (message $message) use ($context) {
						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Cancel
	 *
	 * Ending the account localization create process
	 * without creating the localization record in the database
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function cancel(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing localization 
			$localization = $context->get('localization');

			if ($localization) {
				// Initialized localization

				// Reading from the telegram user buffer
				$context->getUserDataItem(static::PROCESS)
					->then(function (?array $process) use ($context, $localization) {
						// Readed from the telegram user buffer

						if ($process) {
							// Found started account localization create process

							// Deleting in the telegram user buffer
							$context->deleteUserDataItem(static::PROCESS)
								->then(function () use ($context, $localization) {
									// Deleted in the telegram user buffer

									// Sending the message
									$context->sendMessage('🗑 *' . $localization['account_localization_create_canceled'] . '*')
										->then(function (message $message) use ($context) {
											// Sended the message

											// Ending the conversation process
											$context->endConversation();

											// Sending the account localizations menu
											telegram_account::localizations($context);
										});
								});
						} else {
							// Not found started account localization create process

							// Sending the message
							$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_started'] . '*')
								->then(function (message $message) use ($context) {
									// Sended the message

									// Ending the conversation process
									$context->endConversation();

									// Sending the account localizations menu
									telegram_account::localizations($context);
								});
						}
					});
			} else {
				// Not initialized localization

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize localization*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * End
	 *
	 * Ending the account localization create process
	 * and creating the localization record in the database
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function end(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Reading from the telegram user buffer
					$context->getUserDataItem(static::PROCESS)
						->then(function (?array $process) use ($context, $account, $language, $localization) {
							// Readed from the telegram user buffer

							if ($process) {
								// Found started account localization create process

								// Initializing the account model
								$model_account = new account;

								// Creating the account localization
								$created_localization = $model_account->localization->create(
									account: $account->identifier,
									language: $process['language'],
									name: $process['name']
								);

								if ($created_localization) {
									// Created the account localization

									// Sending the message
									$context->sendMessage('✏️ *' . $localization['account_localization_create_created'] . '*')
										->then(function (message $message) use ($context, $account, $language, $localization) {
											// Sended the message

											// Deleting from the telegram user buffer
											$context->deleteUserDataItem(static::PROCESS)
												->then(function () use ($context, $localization) {
													// Deleted from the telegram user buffer

													// Sending the message
													$context->sendMessage('✅ *' . $localization['account_localization_create_completed'] . '*')
														->then(function (message $message) use ($context) {
															// Sended the message

															// Ending the conversation process
															$context->endConversation();

															// Sending the account localizations menu
															telegram_account::localizations($context);
														});
												});
										});
								} else {
									// Not created the account localization

									// Sending the message
									$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_created'] . '*')
										->then(function (message $message) use ($context) {
											// Sended the message

											// Ending the conversation process
											$context->endConversation();
										});
								}
							} else {
								// Not found started account localization create process

								// Sending the message
								$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_started'] . '*')
									->then(function (message $message) use ($context) {
										// Sended the message

										// Ending the conversation process
										$context->endConversation();

										// Sending the account localizations menu
										telegram_account::localizations($context);
									});
							}
						});
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
							// Sended the message

							// Ending the conversation process
							$context->endConversation();
						});
				}
			} else {
				// Not initialized language

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize language*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Menu
	 *
	 * Sends the account localization create menu with parameters: language, name
	 * When all parameters was initialized then sends the complete button
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	protected static function menu(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language) {
				// Initialized language

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Reading from the telegram user buffer
					$context->getUserDataItem(static::PROCESS)
						->then(function (?array $process) use ($context, $account, $language, $localization) {
							// Readed from the telegram user buffer

							if ($process) {
								// Found started account localization create process

								// Initializing the buffer of generated keyboard with languages
								$keyboard = [
									[
										[
											'text' => empty($process['language']) ? '🟢 ' . $localization['account_localization_create_button_language'] : '🟢 ' . $localization['account_localization_create_button_language'] . ': ' . $process['language']->flag() . ' ' . $process['language']->label($language),
											'callback_data' => 'account_localization_create_language'
										]
									],
									[
										[
											'text' => empty($process['name']) ? '🔴 ' . $localization['account_localization_create_button_name'] : '🟢 ' . $localization['account_localization_create_button_name'] . ': ' . $process['name'],
											'callback_data' => 'account_localization_create_name'
										]
									],
								];

								// Initializing the index of last row
								$last = count($keyboard);

								// Initializing the last row
								$keyboard[$last] ??= [];

								// Initializing the button for canceling the generation process
								$keyboard[$last][] = [
									'text' => '❎ ' . $localization['account_localization_create_button_cancel'],
									'callback_data' => 'account_localization_create_cancel'
								];

								if (
									!empty($process['language']) &&
									!empty($process['name'])
								) {
									// Initialized all requeired parameters

									// Initializing the button for completing the generation process
									$keyboard[$last][] = [
										'text' => '✅ ' . $localization['account_localization_create_button_confirm'],
										'callback_data' => 'account_localization_create_end'
									];
								}

								// Ending the conversation process
								$context->endConversation()
									->then(function () use ($context, $localization, $keyboard) {
										// Deinitialized the conversation process

										// Sending the message
										$context->sendMessage(
											'📀 *' . $localization['account_localization_create_generation'] . '*',
											[
												'reply_markup' => [
													'inline_keyboard' => $keyboard,
													'disable_notification' => true,
													'remove_keyboard' => true
												],
											]
										);
									});
							} else {
								// Not found started account localization create process

								// Sending the message
								$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_started'] . '*')
									->then(function (message $message) use ($context) {
										// Sended the message

										// Ending the conversation process
										$context->endConversation();

										// Sending the account localizations menu
										telegram_account::localizations($context);
									});
							}
						});
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
							// Sended the message

							// Ending the conversation process
							$context->endConversation();
						});
				}
			} else {
				// Not initialized language

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize language*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Language
	 *
	 * Write language into the account localization create buffer
	 *
	 * @param context $context Request data from Telegram
	 * @param language $new The language
	 *
	 * @return void
	 */
	public static function language(context $context, language $new): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing language 
			$language = $context->get('language');

			if ($language instanceof language) {
				// Initialized language			

				// Initializing localization 
				$localization = $context->get('localization');

				if ($localization) {
					// Initialized localization

					// Reading from the telegram user buffer
					$context->getUserDataItem(static::PROCESS)
						->then(function (?array $process) use ($context, $account, $language, $localization, $new) {
							// Readed from the telegram user buffer

							if ($process) {
								// Found started account localization create process

								try {
									// Initializing the old language
									$old = $process['language'];

									// Writing into the account localization create process buffer
									$process['language'] = $new;

									// Writing to the telegram user buffer
									$context->setUserDataItem(static::PROCESS, $process)
										->then(function () use ($context, $account, $language, $localization, $new, $old) {
											// Writed to the telegram user buffer

											// Sending the message
											$context->sendMessage('✅ *' . $localization['account_localization_create_language_update_success'] . '* ' . ($old->flag() ? $old->flag() . ' ' : '') . $old->label($language) . ' → *' . ($new->flag() ? $new->flag() . ' ' : '') . $new->label($language) . '*')
												->then(function (message $message) use ($context) {
													// Sended the message

													// Sending the account localization create menu
													static::menu($context);
												});
										});
								} catch (error $error) {
									// Failed to send the message about language update

									// Sending the message
									$context->sendMessage('❎ *' . $localization['account_localization_create_language_update_fail'])
										->then(function (message $message) use ($context) {
											// Sended the message

											// Ending the conversation process
											$context->endConversation();
										});
								}
							} else {
								// Not found started account localization create process

								// Sending the message
								$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_started'] . '*')
									->then(function (message $message) use ($context) {
										// Sended the message

										// Ending the conversation process
										$context->endConversation();

										// Sending the account localizations menu
										telegram_account::localizations($context);
									});
							}
						});
				} else {
					// Not initialized localization

					// Sending the message
					$context->sendMessage('⚠️ *Failed to initialize localization*')
						->then(function (message $message) use ($context) {
							// Sended the message

							// Ending the conversation process
							$context->endConversation();
						});
				}
			} else {
				// Not initialized language

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize language*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}

	/**
	 * Name
	 *
	 * Write name into the account localization create buffer
	 *
	 * @param context $context Request data from Telegram
	 *
	 * @return void
	 */
	public static function name(context $context): void
	{
		// Initializing the account
		$account = $context->get('account');

		if ($account instanceof account) {
			// Initialized the account

			// Initializing localization 
			$localization = $context->get('localization');

			if ($localization) {
				// Initialized localization

				// Reading from the telegram user buffer
				$context->getUserDataItem(static::PROCESS)
					->then(function (?array $process) use ($context, $account, $localization) {
						// Readed from the telegram user buffer

						if ($process) {
							// Found started account localization create process

							// Initializing the new name
							$new = $context->getMessage()->getText();

							if (!empty($new)) {
								// Initialized the new name

								if (mb_strlen($new) >= 2) {
									// Passed minimum length check

									if (mb_strlen($new) <= 128) {
										// Passed maximum length check

										// Search for restricted characters
										preg_match_all('/[\W\d]/u', $new, $matches);

										// Declaring the buffer of found restricted characters
										$characters = [];

										foreach ($matches[0] as $match) {
											// Iterating over found restricted characters 

											if (match ($match) {
												' ', '-' => false,
												default => true
											}) {
												// Found a restricted character

												// Writing into the buffer of found restricted characters
												$characters[] = $match;
											}
										}

										if (empty($characters)) {
											// Not found restricted characters

											try {
												// Initializing the old name
												$old = empty($process['name']) ? '_' . $localization['empty'] . '_' : $process['name'];

												// Writing into the account localization create process buffer
												$process['name'] = $new;

												// Writing to the telegram user buffer
												$context->setUserDataItem(static::PROCESS, $process)
													->then(function () use ($context, $account, $localization, $new, $old) {
														// Writed to the telegram user buffer

														// Escaping characters for markdown
														$escaped = str_replace('-', '\\-', $new);

														// Sending the message
														$context->sendMessage('✅ *' . $localization['account_localization_create_name_update_success'] . "* $old → *$escaped*")
															->then(function (message $message) use ($context) {
																// Sended the message

																// Sending the account localization create menu
																static::menu($context);
															});
													});
											} catch (error $error) {
												// Failed to send the message about name update

												// Sending the message
												$context->sendMessage('❎ *' . $localization['account_localization_create_name_update_fail'])
													->then(function (message $message) use ($context) {
														// Sended the message

														// Ending the conversation process
														$context->endConversation();
													});
											}
										} else {
											// Found restricted characters

											// Initializing title of the message
											$title = '⚠️  *' . $localization['account_localization_create_name_request_restricted_characters_title'] . '*';

											// Initializing description of the message
											$description = '*' . $localization['account_localization_create_name_request_restricted_characters_description'] . '* \\' . implode(', \\', $characters);

											// Sending the message
											$context->sendMessage(
												<<<TXT
												$title

												$description
												TXT
											)
												->then(function (message $message) use ($context) {
													// Sended the message

													// Ending the conversation process
													$context->endConversation();

													// Requesting to enter name again
													button_account_localization_create::name($context);
												});
										}
									} else {
										// Not passed maximum length check

										// Sending the message
										$context->sendMessage('⚠️  *' . $localization['account_localization_create_name_request_too_long'] . '*')
											->then(function (message $message) use ($context) {
												// Sended the message

												// Ending the conversation process
												$context->endConversation();

												// Requesting to enter name again
												button_account_localization_create::name($context);
											});
									}
								} else {
									// Not passed minimum length check

									// Sending the message
									$context->sendMessage('⚠️  *' . $localization['account_localization_create_name_request_too_short'] . '*')
										->then(function (message $message) use ($context) {
											// Sended the message

											// Ending the conversation process
											$context->endConversation();

											// Requesting to enter name again
											button_account_localization_create::name($context);
										});
								}
							} else {
								// Failed to initialize the new name

								// Sending the message
								$context->sendMessage('📄 *' . $localization['account_localization_create_name_request_not_acceptable'] . '*')
									->then(function (message $message) use ($context) {
										// Sended the message

										// Ending the conversation process
										$context->endConversation();

										// Requesting to enter name again
										button_account_localization_create::name($context);
									});
							}
						} else {
							// Not found started account localization create process

							// Sending the message
							$context->sendMessage('⚠️ *' . $localization['account_localization_create_not_started'] . '*')
								->then(function (message $message) use ($context) {
									// Sended the message

									// Ending the conversation process
									$context->endConversation();

									// Sending the account localizations menu
									telegram_account::localizations($context);
								});
						}
					});
			} else {
				// Not initialized localization

				// Sending the message
				$context->sendMessage('⚠️ *Failed to initialize localization*')
					->then(function (message $message) use ($context) {
						// Sended the message

						// Ending the conversation process
						$context->endConversation();
					});
			}
		} else {
			// Not initialized the account

			// Sending the message
			$context->sendMessage('⚠️ *Failed to initialize the account*')
				->then(function (message $message) use ($context) {
					// Sended the message

					// Ending the conversation process
					$context->endConversation();
				});
		}
	}
}
