<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

// Правильный способ подключения js, с использованием нового ядра D7
use \Bitrix\Main\Page\Asset;
Asset::getInstance()->addJs($templateFolder . '/js/jquery.form.min.js');
Asset::getInstance()->addJs($templateFolder . '/js/jquery.ladda.production.min.js');
Asset::getInstance()->addJs($templateFolder . '/js/jquery.magnificpopup.min.js');
Asset::getInstance()->addJs($templateFolder . '/js/cn.custom.form.js');