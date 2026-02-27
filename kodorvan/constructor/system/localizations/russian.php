<?php

// Выход (успех)
return [
	// Система
	'empty' => 'Пусто',
	'yes' => 'Да',
	'no' => 'Нет',

	// Главное меню
	'menu_title' => 'Главное меню',
	'menu_description_guest' => "🔥 *Создайте ваш первый проект* и получите *ориентировочную стоимость* всего за 2 минуты",
	'menu_description_partner' => "*Благодарю за выбор нашей команды*\. Теперь Вы один из наших %d партнёров!",
	'menu_update' => 'Последнее обновление',
	'menu_button_project_new' => 'Создать',
	'menu_button_projects' => 'Проекты',
	'menu_button_operator' => 'Связь с оператором',

	// Аккаунт
	'account_title' => 'Аккаунт',
	'account_authorized_system' => 'Доступ к системе',
	'account_authorized_settings' => 'Доступ к изменению настроек',
	'account_authorized_system_settings' => 'Системный доступ к системным настройкам',

	// Проект: создание
	'project_create_title' => 'Создание проекта',
	/* 'project_create_description' => "Расчитайте ориентировочное время разработки, затем выберите разработчиков и получите стоимость\n\nПосле расчётов можно будет отправить проект в заказ разработчикам и приложить ТЗ, либо краткое описание задачи\n\nМы погружаемся в проекты полностью, поэтому стараемся не распыляться - от степени нагрузки меняется коэффициент стоимости!", */
	'project_create_description' => "Задайте параметры и получите ориентировочное время разработки, затем выберите разработчиков и получите стоимость их работы\n\n_После расчётов можно отправить проект в заказ и приложить ТЗ, либо описание задачи_",
	'project_create_time' => 'Время разработки',
	'project_create_time_hours' => 'ч',
	'project_create_time_hours_from' => 'от',
	'project_create_button_back' => 'Назад',
	'project_create_button_request' => 'Заказать',

	'project_create_architectures_title' => 'Выбор архитектуры проекта',
	'project_create_architectures_description' => 'Каждая архитектура имеет уникальные параметры и коэффициенты \- это основа дальнейших расчётов\!',
	'project_create_button_architecture' => 'Архитектура',
	'project_create_button_architecture_selected' => 'Архитектура',

	'project_create_purposes_title' => 'Выбор назначения',
	'project_create_purposes_description' => 'Вектор разработки, основание проекта',
	'project_create_button_purpose' => 'Назначение',
	'project_create_button_purpose_selected' => 'Назнач.',

	'project_create_integrations_title' => 'Выбор интеграций',
	'project_create_integrations_description' => "Синхронизация данных в реальном времени, скачивание, загрузка, перенос информации, админ\-панель, рассылка сообщений, подключение аккаунтов\.\.\.\n\n_Отправка запросов в *API*, генерация и перехват *HTTP\-сообщений*, *эмуляция* действий пользователя через *виртуальный браузер* с курсором мыши и клавиатурой, либо *нестандартные протоколы связи*_",
	'project_create_button_integrations' => 'Интеграции',
	'project_create_button_integrations_selected' => 'Интеграции',

	'project_create_requested' => 'Проект создан и отправлен оператору',
	'project_create_cancelled' => 'Создание проекта отменено',

	'project_request_title' => 'Заказ #%d',
	'project_request_architecture' => 'Архитектура',
	'project_request_purpose' => 'Назначение',
	'project_request_hours' => 'Часы',
	'project_request_cost' => 'Стоимость',
	'project_request_command' => 'Команда',
	'project_request_empty' => 'Пусто',
	'project_request_button_accept' => 'Принять',
	'project_request_button_refuse' => 'Отказать',
	'project_request_button_edit' => 'Редактировать',
	'project_request_button_chat' => 'Чат с заказчиком',

	// Проект: типы
	'project_architecture_chat_robot' => 'Чат-робот',
	'project_architecture_parser' => 'Парсер',
	'project_architecture_calculator' => 'Калькулятор',
	'project_architecture_crm' => 'CRM',
	'project_architecture_site' => 'Сайт',
	'project_architecture_program' => 'Программа',
	'project_architecture_complex' => 'Нестандартная',

	// Проект: назначение
	'project_purpose_funnel' => 'Воронка',
	'project_purpose_contact' => 'Контакты',
	'project_purpose_neural_network' => 'Нейросеть',
	'project_purpose_gallery' => 'Галерея',
	'project_purpose_crm' => 'CRM',
	'project_purpose_landing' => 'Лендинг',
	'project_purpose_marketplace' => 'Маркетплейс',
	'project_purpose_charity' => 'Благотворительность',
	'project_purpose_search' => 'Поиск',
	'project_purpose_calcul+ate' => 'Расчёт',
	'project_purpose_tools' => 'Инструменты',
	'project_purpose_workers' => 'Рабочие',
	'project_purpose_objects' => 'Предметы',
	'project_purpose_events' => 'События',
	'project_purpose_special' => 'Особенный',

	// Проект: интеграции
	'project_integration_one_c' => '1C',
	'project_integration_bitrix24' => 'Битрикс 24',
	'project_integration_moy_sklad' => 'Мой Склад',
	'project_integration_telegram' => 'Телеграм',
	'project_integration_mail' => 'Почта',
	'project_integration_excel' => 'Excel',
	/* 'project_integration_' => '', */

	// Настройки: язык
	'settings_language_title' => 'Выбери язык',
	'settings_language_description' => 'Выбранный язык будет использоваться для генерации системного отображения',
	'settings_language_update_success' => 'Язык заменён',
	'settings_language_update_fail' => 'Не удалось заменить язык',
	'settings_language_button_add' => 'Добавить язык',

	// Авторизация
	'authorization_system' => 'Система',
	'authorization_settings' => 'Настройки',
	'not_authorized_system' => 'У тебя нет доступа к системе',
	'not_authorized_settings' => 'У тебя нет доступа к настройкам',
	'not_authorized_system_settings' => 'У тебя нет доступа к системным настройкам',

	// Сообщения
	'message_initialization_fail' => 'Не удалось инициализировать сообщение Телеграм',
	'message_text_initialization_fail' => 'Не удалось инициализировать текст сообщения Телеграм',

	// Прочее
	'why_so_shroomious' => 'почему такой грибъёзный'
];
