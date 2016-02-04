<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 *	AVAILABLE VARS (PASSED BY LINK)
 * @var link  $component  Ссылка на компонент
 * @var array $arParams   Параметры компонента
 * @var array $arResult   Резалт компонента
 * @var array $arFormData Присланные данные формы
 *
 * обработчикам доступно изменение $arFormData до ее обработки в компоненте
 */
