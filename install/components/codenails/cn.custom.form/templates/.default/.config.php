<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
/**
 * Конфиг формы
 */
return array(
	'FORM_ID'         => 'custom_form_id', // css-идентификатор формы
	'METHOD'          => 'POST', //GET/POST
	'OPEN_TAG_TEXT'   => 'class="col-mb-12 col-6 cn-modal" data-ajax-submit', // произвольный текст, включаемый в открывающий тэг формы
	// конфиг полей формы (общие параметры)
	'FIELDS'          => array(
		'NAME' => array(
			'TYPE'          => 'STRING',
			'NAME'          => 'Ваш Email',
			'REQUIRED'      => 'Y',
			'REG_EXP'       => '',
			'MULTIPLE'      => 'N',
			'DEFAULT'       => '',
			'OPEN_TAG_TEXT' => 'class="input input-block" placeholder="Ваше Email"',
		),
	),
);

/**
 * Доступные конфигурации полей
 * 
 * 	'FIELD_CODE' => array( // NAME ПОЛЯ ФОРМЫ
 * 		'TYPE'          => 'HIDDEN', // тип поля
 * 		'NAME'          => '', // видимая пользователю подпись к полю ИЛИ человеко-понятное название
 * 		'REQUIRED'      => 'N', // Y/N для валидации
 * 		'REG_EXP'       => '', // для валидации
 * 		'IBLOCK_FIELD'  => '', // используется при сохранении в инфоблок
 * 		'MULTIPLE'      => 'N', // Y/N к коду поля добавляются пустые скобки, обработка идет как массива значений
 * 		'DEFAULT'       => '', // исходное значение
 * 		'OPEN_TAG_TEXT' => '', // то же, что и у формы
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'         => 'CUSTOM',
 * 		'NAME'         => '',
 * 		'REQUIRED'     => 'N',
 * 		'REG_EXP'      => '',
 * 		'IBLOCK_FIELD' => '',
 * 		'MULTIPLE'     => 'N',
 * 		'DEFAULT'      => '',
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'              => 'STRING',
 * 		'SIZE'              => '32',
 * 		'ROWS'              => '1',
 * 		'MAXLENGTH'         => '255',
 * 		'NAME'              => '',
 * 		'REQUIRED'          => 'N',
 * 		'REG_EXP'           => '',
 * 		'IBLOCK_FIELD'      => '',
 * 		'DEFAULT'           => '',
 * 		'MULTIPLE'          => 'Y', // Множественное свойство?
 * 		'CAN_ADD'           => 'Y', // Разрешить показ кнопки добавления нового поля
 * 		'OPEN_TAG_TEXT'     => '', // атрибуты поля
 * 		'OPEN_TAG_BTN_TEXT' => '', // атрибуты кнопки добавления нового поля
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'              => 'EMAIL',
 * 		'NAME'              => '',
 * 		'REQUIRED'          => 'N',
 * 		'REG_EXP'           => '',
 * 		'IBLOCK_FIELD'      => '',
 * 		'DEFAULT'           => '',
 * 		'MULTIPLE'          => 'Y', // Множественное свойство?
 * 		'CAN_ADD'           => 'Y', // Разрешить показ кнопки добавления нового поля
 * 		'OPEN_TAG_TEXT'     => '', // атрибуты поля
 * 		'OPEN_TAG_BTN_TEXT' => '', // атрибуты кнопки добавления нового поля
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'              => 'TEL',
 * 		'NAME'              => '',
 * 		'REQUIRED'          => 'N',
 * 		'REG_EXP'           => '',
 * 		'IBLOCK_FIELD'      => '',
 * 		'DEFAULT'           => '',
 * 		'MULTIPLE'          => 'Y', // Множественное свойство?
 * 		'CAN_ADD'           => 'Y', // Разрешить показ кнопки добавления нового поля
 * 		'OPEN_TAG_TEXT'     => '', // атрибуты поля
 * 		'OPEN_TAG_BTN_TEXT' => '', // атрибуты кнопки добавления нового поля
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'              => 'DATE',
 * 		'NAME'              => '',
 * 		'REQUIRED'          => 'N',
 * 		'REG_EXP'           => '',
 * 		'IBLOCK_FIELD'      => '',
 * 		'DEFAULT'           => '',
 * 		'MULTIPLE'          => 'Y', // Множественное свойство?
 * 		'CAN_ADD'           => 'Y', // Разрешить показ кнопки добавления нового поля
 * 		'OPEN_TAG_TEXT'     => '', // атрибуты поля
 * 		'OPEN_TAG_BTN_TEXT' => '', // атрибуты кнопки добавления нового поля
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'              => 'NUMBER',
 * 		'NAME'              => '',
 * 		'REQUIRED'          => 'N',
 * 		'REG_EXP'           => '',
 * 		'IBLOCK_FIELD'      => '',
 * 		'DEFAULT'           => '',
 * 		'MULTIPLE'          => 'Y', // Множественное свойство?
 * 		'CAN_ADD'           => 'Y', // Разрешить показ кнопки добавления нового поля
 * 		'OPEN_TAG_TEXT'     => '', // атрибуты поля
 * 		'OPEN_TAG_BTN_TEXT' => '', // атрибуты кнопки добавления нового поля
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'          => 'CHECKBOX',
 * 		'NAME'          => '',
 * 		'REQUIRED'      => 'N',
 * 		'IBLOCK_FIELD'  => '',
 * 		'DEFAULT'       => 'N',
 * 		'OPEN_TAG_TEXT' => '',
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'          => 'LIST',
 * 		'SIZE'          => '1',
 * 		'FLAGS'         => 'N',
 * 		'AUTOFILLED'    => 'N',
 * 		'VALUES'        => array(),
 * 		'NAME'          => '',
 * 		'REQUIRED'      => 'N',
 * 		'IBLOCK_FIELD'  => '',
 * 		'MULTIPLE'      => 'N',
 * 		'DEFAULT'       => '',
 * 		'OPEN_TAG_TEXT' => '',
 * 	),
 * 
 * 	'FIELD_CODE' => array(
 * 		'TYPE'               => 'FILE',
 * 		'SIZE'               => '32',
 * 		'MAX_FILE_SIZE'      => '0',
 * 		'ALLOWED_FILE_TYPES' => 'png,jpg',
 * 		'NAME'               => '',
 * 		'REQUIRED'           => 'N',
 * 		'IBLOCK_FIELD'       => '',
 * 		'MULTIPLE'           => 'N',
 * 		'OPEN_TAG_TEXT'      => '',
 * 	),
 * 	
 */
