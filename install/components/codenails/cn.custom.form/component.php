<?php

/*
 * Codenails Custom Form
 * Компонент для организации удобных форм
 * с возможностью вызова на AJAX
 * и возможностью добавления элементов в инфоблок,
 * а ещё можно отправлять всё на email,
 * можно модифицировать данные перед оправкой формы, 
 * перед отправкой почтового события
 * и перед добавлением элемента в инфоблок...
 * ну и конечно же никуда без дам и преферанса ^_^
 *
 * @version 2.6.0
 * @date 04.02.2016
 *
 * @author Вадим Солуянов <sallee@info-expert.ru>
 * @author Павел Белоусов <pb@infoexpert.ru>
 * @author Денис Шишкин <ds@infoexpert.ru>
 * @author Мария Недоспасова <mn@infoexpert.ru>
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!function_exists('cnSanitizeData')) {
	function cnSanitizeData($mData) {
		if (is_array($mData)) {
			foreach ($mData as $k => $v) {
				$mData[$k] = cnSanitizeData($v);
			}
		} else {
			$mData = htmlspecialcharsbx($mData);
		}

		return $mData;
	}
}

if (!function_exists('cnDecodeFormData')) {
	function cnDecodeFormData($mData, $isAjax) {
		if ($isAjax && strtolower(LANG_CHARSET) !== 'utf-8') {
			if (function_exists('iconv')) {
				$mData = iconv('UTF-8', LANG_CHARSET . '//IGNORE', $mData);
			} elseif (function_exists('mb_convert_encoding')) {
				$mData = mb_convert_encoding($mData, LANG_CHARSET, "UTF-8");
			}
		}
		return $mData;
	}
}

if (!function_exists('cnIncludeOnPostHandler')) {
	function cnIncludeOnPostHandler(&$arResult, $arParams, $arFormData, $handlerFilePath) {
		include $handlerFilePath;
	}

}

if (!function_exists('cnIncludeOnBeforeAddHandler')) {
	function cnIncludeOnBeforeAddHandler(&$arFields, $arFormData, $handlerFilePath) {
		include $handlerFilePath;
	}
}

if (!function_exists('cnIncludeOnMailHandler')) {
	function cnIncludeOnMailHandler(&$arEventFields, $arResult, $arParams, $handlerFilePath) {
		include $handlerFilePath;
	}
}

if (!function_exists('_cnVerifyFileField')) {
	function _cnVerifyFileField($value, $arConf) {
		if (!empty($arConf['MAX_FILE_SIZE']) && $value['size'] > $arConf['MAX_FILE_SIZE']) {
			return 'FILE_SIZE';
		}
		if (!empty($arConf['ALLOWED_FILE_TYPES'])) {
			$ext = ToLower(end(explode('.', $value['name'])));
			$arValidExt = explode(',', ToLower($arConf['ALLOWED_FILE_TYPES']));
			if (!in_array($ext, $arValidExt)) {
				return 'FILE_TYPE';
			}
		}

		return true;
	}
}

if (!function_exists('cnVerifyDataField')) {
	function cnVerifyDataField($value, $arConf) {
		$result = true;

		if ($arConf['TYPE'] == 'FILE') {
			if ($arConf['MULTIPLE'] != 'Y') {
				$result = _cnVerifyFileField($value, $arConf);
			} else {
				foreach ($value as $v) {
					$result = _cnVerifyFileField($v, $arConf);
					if ($result !== true) {
						break;
					}
				}
			}

			return $result;
		}

		if ($arConf['REQUIRED'] != 'Y' && empty($arConf['REG_EXP'])) {
			return true;
		}

		if (is_array($value)) {
			foreach ($value as $v) {
				$res = cnVerifyDataField($v, $arConf);
				if ($res !== true) {
					$result = $res;
					break;
				}
			}
		} else {
			if ($arConf['REQUIRED'] == 'Y' && empty($value)) {
				$result = 'EMPTY';
			} elseif (!empty($arConf['REG_EXP']) && !preg_match($arConf['REG_EXP'], $value, $match)) {
				$result = 'WRONG';
			}
		}

		return $result;
	}
}

/**
 * проверка входных параметров и установка значений по-умолчанию
 */
$arParams['FORM_CODE'] = trim($arParams['FORM_CODE']);
if (empty($arParams['FORM_CODE'])) {
	$arParams['FORM_CODE'] = 'custom';
}
$arParams['IS_AJAX'] = (!empty($_REQUEST['ajaxid']) && $_REQUEST['ajaxid'] == $arParams['FORM_CODE']);
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
$arParams['USE_IBLOCK'] = $arParams['USE_IBLOCK'] == 'Y' && $arParams['IBLOCK_ID'] > 0;
$arParams['PARENT_SECTION_ID'] = intval($arParams['PARENT_SECTION_ID']);
$arParams['AJAX_OPEN_FORM'] = ($arParams['AJAX_OPEN_FORM'] == 'Y');
$arParams['ENABLE_FAKE_FORM'] = ($arParams['ENABLE_FAKE_FORM'] == 'Y');
$arParams['EVENT_NAME'] = trim($arParams['EVENT_NAME']);
$arParams['SEND_NOTIFICATION'] = $arParams['SEND_NOTIFICATION'] == 'Y' && !empty($arParams['EVENT_NAME']);

if(is_array($arParams['SHOW_FOR_USER_GROUPS'])) {
	TrimArr($arParams['SHOW_FOR_USER_GROUPS']);
} else {
	$arParams['SHOW_FOR_USER_GROUPS'] = array();
}
if(is_array($arParams['ENABLE_FOR_USER_GROUPS'])) {
	TrimArr($arParams['ENABLE_FOR_USER_GROUPS']);
}else {
	$arParams['ENABLE_FOR_USER_GROUPS'] = array();
}

$arParams['NOT_ENABLE_NOTE'] = trim($arParams['NOT_ENABLE_NOTE']);
$arParams['CACHE_TIME'] = intval($arParams['CACHE_TIME']);

/**
 *	проверка прав доступа к форме
 */

$arUserGroups = $USER->GetUserGroupArray();
$arParams['USER_CAN_POST'] = true;

if (!empty($arParams['SHOW_FOR_USER_GROUPS']) && !count(array_intersect($arParams['SHOW_FOR_USER_GROUPS'], $arUserGroups)) && !$USER->IsAdmin()) {
	$arParams['USER_CAN_POST'] = false;
}


/**
 * инициализация шаблона компонента
 * загрузка конфигурационного файла из папки шаблона (запоминаем в arParams)
 * проверка наличия on_post_handlers.php в папке шаблона (запоминаем полный путь к файлу в arParams)
 */
$this->InitComponentTemplate();
$obTemplate = $this->GetTemplate();
if (!is_object($obTemplate)) {
	if ($USER->IsAdmin()) {
		ShowError('obTemplate not an object');
	}
	return;
}
$templateFile = $obTemplate->GetFile();
$templateFolder = dirname($templateFile);
$configFile = $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/.config.php';
$handlerFile = $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/on_post_handlers.php';
$beforeAddHandlerFile = $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/on_before_add_handlers.php';
if (!is_readable($handlerFile)) {
	$handlerFile = '';
}
$mailHandlerFile = $_SERVER['DOCUMENT_ROOT'] . $templateFolder . '/on_mail_handlers.php';
if (!is_readable($mailHandlerFile)) {
	$mailHandlerFile = '';
}
if (!is_readable($configFile)) {
	if ($USER->IsAdmin()) {
		ShowError('Form config not found');
	}
	return;
}
$arParams['CONF'] = include $configFile;

/**
 * проверка корректности конфига из шаблона
 */
$arFieldTypeSupported = array(
	'EMAIL', 'DATE', 'NUMBER', 'TEL', 'STRING', 'HIDDEN', 'CUSTOM', 'CHECKBOX', 'LIST', 'FILE',
);
$bWasFile = false;
if (is_array($arParams['CONF']['FIELDS'])) {
	foreach ($arParams['CONF']['FIELDS'] as $code => $fieldConf) {
		if (!in_array($fieldConf['TYPE'], $arFieldTypeSupported)) {
			unset($arParams['CONF']['FIELDS'][$code]);
		}
		if ($fieldConf['TYPE'] == 'FILE') {
			$bWasFile = true;
		}
	}
}

$bConfCorrect = is_array($arParams['CONF'])
&& is_array($arParams['CONF']['FIELDS'])
&& count($arParams['CONF']['FIELDS']);

if (!$bConfCorrect) {
	if ($USER->IsAdmin()) {
		ShowError('Incorrect form config');
	}
	return;
}

$arParams['FORM'] = array();
$arParams['FORM']['FORM_ID'] = (!preg_match('/^[a-zA-Z0-9_-]+$/', $arParams['CONF']['FORM_ID'])) ? 'custom_form_id' : $arParams['CONF']['FORM_ID'];
$arParams['FORM']['METHOD'] = (!in_array(ToUpper($arParams['CONF']['FORM_ID']), array('GET', 'POST'))) ? 'POST' : ToUpper($arParams['CONF']['FORM_ID']);
$arParams['FORM']['OPEN_TAG_TEXT'] = trim($arParams['CONF']['OPEN_TAG_TEXT']);
$arParams['FORM']['OPEN_TAG_BTN_TEXT'] = trim($arParams['CONF']['OPEN_TAG_BTN_TEXT']);

$arParams['FORM']['ACTION'] = $APPLICATION->GetCurPageParam('', array('data', 'submit_' . $arParams['FORM_CODE'], 'custom_form_code'));
$enctype = ($bWasFile) ? ' enctype="multipart/form-data"' : '';

if (!$arParams['AJAX_OPEN_FORM'] && $arParams['ENABLE_FAKE_FORM']) {
	$arParams['FORM']['HTML_OPEN'] = '<div id="' . $arParams['FORM']['FORM_ID']
	. '" data-fake-form=\'name="custom_form" method="' . $arParams['FORM']['METHOD']
	. '" action="' . $arParams['FORM']['ACTION'] . '" '
	. $enctype . ' '
	. $arParams['FORM']['OPEN_TAG_TEXT']
	. '\'>';
	$arParams['FORM']['HTML_CLOSE'] = '</div>';
} else {
	$arParams['FORM']['HTML_OPEN'] = '<form id="' . $arParams['FORM']['FORM_ID']
	. '" name="custom_form" method="' . $arParams['FORM']['METHOD']
	. '" action="' . $arParams['FORM']['ACTION'] . '" '
	. $enctype . ' '
	. $arParams['FORM']['OPEN_TAG_TEXT']
	. '>';
	$arParams['FORM']['HTML_CLOSE'] = '</form>';
}

/**
 * если аякс-запрос требуется очистить буфер вывода
 */
if ($arParams['IS_AJAX']) {
	$APPLICATION->RestartBuffer();
}

/**
 * проверка сабмита формы и обработка, подключение после получения всех данных формы (но до каких-либо дальнейших действий) on_post_handlers.php
 * затем проверка валидности значений
 * сохранение в инфоблок, если задано
 * отправка уведомлений, если задано
 * если ни то, ни другое не задано - форма нихрена не делает.
 * не аякс:
 *	если ошибка - продолжаем выполнение компонента, ок - редирект на себя с удалением из GET имен, используемых в форме
 * аякс:
 *	возврат JSON-результата, включая ошибки, в вызывавший JS
подключение epilog_after
выход
 */
$arParams['IS_POST'] = $_SERVER['REQUEST_METHOD'] == $arParams['FORM']['METHOD']
&& $_REQUEST['custom_form_code'] == $arParams['FORM_CODE']
&& $arParams['USER_CAN_POST'];

if ($arParams['IS_POST']) {

	$arFormData = $_REQUEST['data'];
	$arResult['VALUES'] = $arResult['~VALUES'] = array();
	$arPostFiles = array();

	// Collect files into more simple array
	if ($bWasFile && !empty($_FILES)) {

		foreach ($_FILES as $fieldName => $arFile) {

			if (is_array($arFile['name'])) {

				$arSubFields = array_keys($arFile['name']);

				foreach ($arSubFields as $subFieldName) {
					if (!is_array($arFile['name'][$subFieldName])) {
						$arPostFiles[$fieldName][$subFieldName] = array(
							'name' => $arFile['name'][$subFieldName],
							'type' => $arFile['type'][$subFieldName],
							'tmp_name' => $arFile['tmp_name'][$subFieldName],
							'error' => $arFile['error'][$subFieldName],
							'size' => $arFile['size'][$subFieldName],
						);
					} else {
						$arSubSubFields = array_keys($arFile['name'][$subFieldName]);
						foreach ($arSubSubFields as $subSubFieldName) {
							if (!is_array($arFile['name'][$subFieldName][$subSubFieldName])) {
								$arPostFiles[$fieldName][$subFieldName][$subSubFieldName] = array(

									'name' => $arFile['name'][$subFieldName][$subSubFieldName],
									'type' => $arFile['type'][$subFieldName][$subSubFieldName],
									'tmp_name' => $arFile['tmp_name'][$subFieldName][$subSubFieldName],
									'error' => $arFile['error'][$subFieldName][$subSubFieldName],
									'size' => $arFile['size'][$subFieldName][$subSubFieldName],
								);
							}
						}
					}
				}
			} else {
				$arPostFiles[$fieldName] = $arFile;
			}

		}
	}

	foreach ($arParams['CONF']['FIELDS'] as $code => $arConf) {
		if ($arConf['TYPE'] != 'FILE') {
			$arResult['VALUES'][$code] = cnSanitizeData(cnDecodeFormData($arFormData[$code], $arParams['IS_AJAX']));
			$arResult['~VALUES'][$code] = cnDecodeFormData($arFormData[$code], $arParams['IS_AJAX']);
		} else {
			if (!empty($arPostFiles['data'][$code]['name'])) {
				if (empty($arPostFiles['data'][$code]['error'])) {
					$arResult['VALUES'][$code] = $arResult['~VALUES'][$code] = $arPostFiles['data'][$code];
				}
			} else {
				$arResult['VALUES'][$code] = array();
				foreach ($arPostFiles['data'][$code] as $arF) {
					if (empty($arF['error'])) {
						$arResult['VALUES'][$code][] = $arResult['~VALUES'][$code][] = $arF;
					}
				}
			}
		}
	}

	// проверка корректности и постобработка данных
	$arErrors = array();
	if (!empty($handlerFile)) {
		cnIncludeOnPostHandler($arResult, $arParams, $arFormData, $handlerFile);
	}

	foreach ($arParams['CONF']['FIELDS'] as $code => $arConf) {
		$res = cnVerifyDataField($arResult['~VALUES'][$code], $arParams['CONF']['FIELDS'][$code]);
		if ($res !== true) {
			$arErrors[$code] = $res;
		}
	}

	if (empty($arErrors)) {
		// обработка формы - отправка уведомления и/или сохранение в инфоблок
		if ($arParams['USE_IBLOCK'] && CModule::IncludeModule('iblock')) {
			$arIBlockFields = array(
				'NAME',
				'CODE',
				'ACTIVE',
				'DATE_ACTIVE_FROM',
				'DATE_ACTIVE_TO',
				'DATE_CREATE',
				'CREATED_BY',
				'PREVIEW_TEXT',
				'DETAIL_TEXT',
				'PREVIEW_PICTURE',
				'DETAIL_PICTURE',
				'SORT',
				'XML_ID',
				'IBLOCK_SECTION_ID',
			);

			$arIBlockProps = array();
			$dbProps = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y'));
			$arValidPropTypes = array('S', 'N', 'L', 'E', 'G', 'F');
			while ($arProp = $dbProps->Fetch()) {
				if (in_array($arProp['PROPERTY_TYPE'], $arValidPropTypes)) {
					$arIBlockProps[$arProp['CODE']] = $arProp;
				}
			}

			$arElementAdd = array(
				'IBLOCK_ID' => $arParams['IBLOCK_ID'],
				'IBLOCK_SECTION_ID' => $arParams['PARENT_SECTION_ID'],
				'ACTIVE' => $arParams['ACTIVE_ITEM'],
				'DATE_ACTIVE_FROM' => ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL"),
			);
			$propPrefix = 'PROPERTY_';

			foreach ($arParams['CONF']['FIELDS'] as $code => $arConf) {
				if (!empty($arConf['IBLOCK_FIELD'])) {

					if (strpos($arConf['IBLOCK_FIELD'], $propPrefix) === 0) {
						$propCode = substr($arConf['IBLOCK_FIELD'], strlen($propPrefix));
						if (!array_key_exists($propCode, $arIBlockProps)) {
							continue;
						}
						$propID = $arIBlockProps[$propCode]['ID'];
						$propType = $arIBlockProps[$propCode]['PROPERTY_TYPE'];
						$propUserType = $arIBlockProps[$propCode]['USER_TYPE'];
						if ($propUserType == 'HTML') {
							$arElementAdd['PROPERTY_VALUES'][$propID] = array(
								'VALUE' => $arResult['~VALUES'][$code],
								'TYPE' => 'text',
							);
						} else {
							$arElementAdd['PROPERTY_VALUES'][$propID] = $arResult['~VALUES'][$code];
						}
					} elseif (in_array($arConf['IBLOCK_FIELD'], $arIBlockFields)) {
						$arElementAdd[$arConf['IBLOCK_FIELD']] = $arResult['~VALUES'][$code];
					}
				} elseif (in_array($code, $arIBlockFields)) {
					$arElementAdd[$code] = $arResult['~VALUES'][$code];
				}
			}

			if (!empty($beforeAddHandlerFile)) {
				cnIncludeOnBeforeAddHandler($arElementAdd, $arFormData, $beforeAddHandlerFile);
			}

			$obElm = new CIBlockElement;
			$elmID = $obElm->Add($arElementAdd);
			if (!$elmID) {
				$arErrors['IBLOCK'] = 'IBLOCK_ADD_ERROR';
			}
		}

		if (empty($arErrors) && $arParams['SEND_NOTIFICATION']) {
			$dbEvent = CEventType::GetByID($arParams['EVENT_NAME'], 'ru');
			if ($arEvent = $dbEvent->Fetch()) {

				$arEventFields = array(
					'USER_ID' => $USER->GetID(),
					'USER_LOGIN' => $USER->GetLogin(),
					'USER_EMAIL' => $USER->GetEmail(),
					'USER_FULLNAME' => $USER->GetFullName(),

					'CURRENT_URL' => $APPLICATION->GetCurPage(),
					'COMPONENT_NAME' => $this->GetName(),
					'TEMPLATE_FILE' => $templateFile,
					'IBLOCK_ID' => ($arParams['USE_IBLOCK'] ? $arParams['IBLOCK_ID'] : ''),
				);

				foreach ($arParams['CONF']['FIELDS'] as $code => $arConf) {
					if ($arConf['TYPE'] != 'FILE') {
						$arEventFields['EV_' . $code] =
						(is_array($arResult['VALUES'][$code]))
						?
						implode(', ', $arResult['VALUES'][$code])
						: $arResult['VALUES'][$code];
					} else {
						if (empty($arResult['VALUES'][$code]['name'])) {
							$arEventFields['EV_' . $code] = '';
							$comma = '';
							foreach ($arResult['VALUES'][$code] as $arF) {
								$arEventFields['EV_' . $code] .= $comma . $arF['name'];
								$comma = ', ';
							}
						} else {
							$arEventFields['EV_' . $code] = $arResult['VALUES'][$code]['name'];
						}
					}
				}

				if($elmID) $arResult['ID'] = $elmID;

				if (!empty($mailHandlerFile)) {
					cnIncludeOnMailHandler($arEventFields, $arResult, $arParams, $mailHandlerFile);
				}

				$evID = CEvent::Send($arParams['EVENT_NAME'], SITE_ID, $arEventFields);
				if (!$evID) {
					$arErrors['MAIL'] = 'SEND_EMAIL_ERROR';
				}
			}
		}
	}

	if (empty($arErrors)) {
		$redirectUrl = $APPLICATION->GetCurPageParam('', array('data', 'submit_' . $arParams['FORM_CODE'], 'custom_form_code'));
		$_SESSION['CN_CUSTOM_FORM_' . $arParams['FORM_CODE']] = 'OK';
		LocalRedirect($redirectUrl);
		exit;
	} else {
		$arResult['ERRORS'] = $arErrors;
	}
}// end of post

/**
 * подготавливаем данные для формы
 */
if ($arParams['IS_POST'] || !empty($_SESSION['CN_CUSTOM_FORM_' . $arParams['FORM_CODE']])) {
	$arParams['CACHE_TIME'] = 0;
}

if ($this->StartResultCache($arParams['CACHE_TIME'], $USER->GetGroups())) {
	$arResult['FIELDS'] = array();
	$arResult['HIDDEN_FIELDS'] = array();

	foreach ($arParams['CONF']['FIELDS'] as $code => $arConf) {
		$regexp = (strlen($arConf['REG_EXP'])) ? ' data-regexp="' . $arConf['REG_EXP'] . '"' : '';
		$required = ($arConf['REQUIRED'] == 'Y') ? ' required="required"' : '';
		$multipleSuffix = ($arConf['MULTIPLE'] == 'Y') ? '[]' : '';
		$value = ($arParams['IS_POST']) ? $arResult['VALUES'][$code] : '';
		if (empty($value) && !empty($arConf['DEFAULT'])) {
			$value = htmlspecialcharsbx($arConf['DEFAULT']);
		}
		if ($arConf['MULTIPLE'] == 'Y' && !is_array($value)) {
			$value = array($value);
		}

		switch ($arConf['TYPE']) {
			case 'CUSTOM':
				break;

			case 'LIST':
				if (!is_array($arConf['VALUES']) || !count($arConf['VALUES'])) {
					break;
				}
				if ($arConf['FLAGS'] == 'Y') {
					$htmlType = ($arConf['MULTIPLE'] == 'Y') ? 'checkbox' : 'radio';
					$html = array();
					foreach ($arConf['VALUES'] as $fieldValue => $fieldName) {

						$for = $htmlType . '_' . crc32($htmlType . $code . $fieldName);

						$html[] = array(
							'TAG' => '<input id="' . $for . '" type="' . $htmlType . '" name="data[' . $code . ']' . $multipleSuffix . '"' . $regexp . $required . ' value="' . $fieldValue . '"' . (in_array($fieldValue, $value) ? ' checked' : '') . ' ' . $arConf['OPEN_TAG_TEXT'] . ' />',
							'LABEL' => htmlspecialcharsbx($fieldName),
							'FOR' => $for,
						);
					}

					$arResult['FIELDS'][$code] = array(
						'NAME' => 'data[' . $code . ']' . $multipleSuffix,
						'TYPE' => $htmlType,
						'LABEL' => $arConf['NAME'],
						'VALUE' => $value,
						'HTML' => $html,
					);
				} else {
					if (intval($arConf['SIZE'])) {
						$arConf['OPEN_TAG_TEXT'] .= ' size="' . intval($arConf['SIZE']) . '"';
					}
					$html = '<select name="data[' . $code . ']' . $multipleSuffix . '"' . $regexp . $required . ' ' . $arConf['OPEN_TAG_TEXT'] . '>' . "\n";
					if ($arConf['REQUIRED'] != 'Y') {
						$html .= '<option value="">&nbsp;</option>' . "\n";
					}
					foreach ($arConf['VALUES'] as $fieldValue => $fieldName) {
						if (strpos($fieldValue, 'IS_EMPTY_VALUE') === false) {
							$_fieldValue = $fieldValue;
						} else {
							$_fieldValue = '';
						}
						$selected = (($arConf['MULTIPLE'] == 'Y' && in_array($fieldValue, $value)) || ($arConf['MULTIPLE'] != 'Y' && $fieldValue == $value)) ? 'selected' : '';

						$html .= '<option value="' . $_fieldValue . '" ' . $selected . '>' . $fieldName . '</option>' . "\n";

					}
					$html .= '</select>';
					$arResult['FIELDS'][$code] = array(
						'NAME' => 'data[' . $code . ']' . $multipleSuffix,
						'TYPE' => 'select',
						'LABEL' => $arConf['NAME'],
						'VALUE' => $value,
						'HTML' => $html,
					);
				}
				break;

			case 'HIDDEN':
				$html = '<input type="hidden" name="data[' . $code . ']"' . $regexp . $required . ' value="' . $value . '" ' . $arConf['OPEN_TAG_TEXT'] . ' />';
				$arResult['FIELDS'][$code] = array(
					'NAME' => 'data[' . $code . ']',
					'TYPE' => 'hidden',
					'LABEL' => $arConf['NAME'],
					'VALUE' => $value,
					'HTML' => $html,
				);
				break;

			case 'CHECKBOX':
				$html = '<input type="checkbox" name="data[' . $code . ']"' . $required . ' value="Y"' . ($value == 'Y' ? ' checked' : '') . ' ' . $arConf['OPEN_TAG_TEXT'] . ' />';
				$arResult['FIELDS'][$code] = array(
					'NAME' => 'data[' . $code . ']',
					'TYPE' => 'checkbox',
					'LABEL' => $arConf['NAME'],
					'VALUE' => $value,
					'HTML' => $html,
				);
				break;

			case 'EMAIL':
			case 'DATE':
			case 'NUMBER':
			case 'TEL':
				if (intval($arConf['MAXLENGTH'])) {
					$arConf['OPEN_TAG_TEXT'] .= ' maxlength="' . intval($arConf['MAXLENGTH']) . '"';
				}
				if ($arConf['MULTIPLE'] == 'Y') {
					$html = '';
					foreach ($value as $key => $val) {
						$html .= '<input type="' . strtolower($arConf['TYPE']) . '" name="data[' . $code . '][' . $key . ']"' . $regexp . $required . ' value="' . $val . '" ' . $arConf['OPEN_TAG_TEXT'] . ' />';

						if (count($value) != $key + 1) {
							$html .= '<br />';
						}

					}
					if ($arConf['CAN_ADD'] == 'Y') {$html .= '<span ' . $arConf['OPEN_TAG_BTN_TEXT'] . ' data-cn-form-name="data[' . $code . '][]">+</span>';	}
					$arResult['FIELDS'][$code] = array(
						'NAME' => 'data[' . $code . ']',
						'TYPE' => strtolower($arConf['TYPE']),
						'LABEL' => $arConf['NAME'],
						'VALUE' => $value,
						'HTML' => $html,
					);

				} else {
					$html = '<input type="' . strtolower($arConf['TYPE']) . '" name="data[' . $code . ']"' . $regexp . $required . ' value="' . $value . '" ' . $arConf['OPEN_TAG_TEXT'] . ' />';
					$arResult['FIELDS'][$code] = array(
						'NAME' => 'data[' . $code . ']',
						'TYPE' => strtolower($arConf['TYPE']),
						'LABEL' => $arConf['NAME'],
						'VALUE' => $value,
						'HTML' => $html,
					);
				}
				break;

			case 'FILE':
				if ($arConf['MULTIPLE'] == 'Y') {
					$arConf['OPEN_TAG_TEXT'] .= ' multiple ';
				}

				$html = '<input type="' . strtolower($arConf['TYPE']) . '" name="data[' . $code . ']' . $multipleSuffix . '"' . $regexp . $required . ' value="" ' . $arConf['OPEN_TAG_TEXT'] . ' />';
				$arResult['FIELDS'][$code] = array(
					'NAME' => 'data[' . $code . ']',
					'TYPE' => strtolower($arConf['TYPE']),
					'LABEL' => $arConf['NAME'],
					'VALUE' => $value,
					'HTML' => $html,
				);

				break;

			default:
				if (intval($arConf['SIZE'])) {
					$arConf['OPEN_TAG_TEXT'] .= ' size="' . intval($arConf['SIZE']) . '"';
				}
				if (intval($arConf['ROWS']) < 2) {
					if (intval($arConf['MAXLENGTH'])) {
						$arConf['OPEN_TAG_TEXT'] .= ' maxlength="' . intval($arConf['MAXLENGTH']) . '"';
					}
					if ($arConf['MULTIPLE'] == 'Y') {
						$html = '';
						foreach ($value as $key => $val) {
							$html .= '<input type="text" name="data[' . $code . '][' . $key . ']"' . $regexp . $required . ' value="' . $val . '" ' . $arConf['OPEN_TAG_TEXT'] . ' />';
							if (count($value) != $key + 1) {
								$html .= '<br />';
							}

						}
						if ($arConf['CAN_ADD'] == 'Y') {$html .= '<span ' . $arConf['OPEN_TAG_BTN_TEXT'] . ' data-cn-form-name="data[' . $code . '][]">+</span>';	}
						$arResult['FIELDS'][$code] = array(
							'NAME' => 'data[' . $code . ']',
							'TYPE' => 'text',
							'LABEL' => $arConf['NAME'],
							'VALUE' => $value,
							'HTML' => $html,
						);
					} else {
						$html = '<input type="text" name="data[' . $code . ']"' . $regexp . $required . ' value="' . $value . '" ' . $arConf['OPEN_TAG_TEXT'] . ' />';
						$arResult['FIELDS'][$code] = array(
							'NAME' => 'data[' . $code . ']',
							'TYPE' => 'text',
							'LABEL' => $arConf['NAME'],
							'VALUE' => $value,
							'HTML' => $html,
						);
					}
				} else {
					$arConf['OPEN_TAG_TEXT'] .= ' rows="' . intval($arConf['ROWS']) . '"';
					if ($arConf['MULTIPLE'] == 'Y') {
						$html = '';
						foreach ($value as $key => $val) {
							$html .= '<textarea name="data[' . $code . '][' . $key . ']"' . $regexp . $required . ' ' . $arConf['OPEN_TAG_TEXT'] . ' >' . $val . '</textarea>';
							if (count($value) != $key + 1) {
								$html .= '<br />';
							}

						}
						if ($arConf['CAN_ADD'] == 'Y') {$html .= '<span ' . $arConf['OPEN_TAG_BTN_TEXT'] . ' data-cn-form-name="data[' . $code . '][]">+</span>';	}
						$arResult['FIELDS'][$code] = array(
							'NAME' => 'data[' . $code . ']',
							'TYPE' => 'textarea',
							'LABEL' => $arConf['NAME'],
							'VALUE' => $value,
							'HTML' => $html,
						);
					} else {
						$html = '<textarea name="data[' . $code . ']"' . $regexp . $required . ' ' . $arConf['OPEN_TAG_TEXT'] . ' >' . $value . '</textarea>';
						$arResult['FIELDS'][$code] = array(
							'NAME' => 'data[' . $code . ']',
							'TYPE' => 'textarea',
							'LABEL' => $arConf['NAME'],
							'VALUE' => $value,
							'HTML' => $html,
						);
					}
				}
				break;
		}
	}

	$arResult['HIDDEN_FIELDS']['custom_form_code'] = array(
		'NAME' => 'custom_form_code',
		'TYPE' => 'hidden',
		'LABEL' => 'form unique code',
		'VALUE' => htmlspecialcharsbx($arParams['FORM_CODE']),
		'HTML' => '<input type="hidden" name="custom_form_code" value="' . htmlspecialcharsbx($arParams['FORM_CODE']) . '" />',
	);
/**
 * определяем имя шаблона: если не аякс + параметр открытия формы через аякс - open_form иначе template (можно пустую строку)
 */
	$template = (!$arParams['IS_AJAX'] && $arParams['AJAX_OPEN_FORM']) ? 'open_form' : '';
	if (!empty($_SESSION['CN_CUSTOM_FORM_' . $arParams['FORM_CODE']])) {
		$arResult['POST_SUCCESS'] = true;
		unset($_SESSION['CN_CUSTOM_FORM_' . $arParams['FORM_CODE']]);
	}

	$this->IncludeComponentTemplate($template);

}
/**
 * если аякс-запрос требуется прекратить выполнение страницы, но при этом корректно завершить работу битрикса
 * подключить epilog_after
 * выйти из php
 */
if ($arParams['IS_AJAX']) {
	require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php";
	exit;
}
