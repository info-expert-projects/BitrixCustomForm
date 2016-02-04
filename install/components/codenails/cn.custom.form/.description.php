<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$arComponentDescription = array(
	"NAME"        => Loc::GetMessage("CN_CUSTOM_FORM_NAME"),
	"DESCRIPTION" => Loc::GetMessage("CN_CUSTOM_FORM_DESC"),
	"ICON"        => "/images/icon.png",
	"SORT"        => 100,
	"CACHE_PATH"  => "Y",
	'COMPLEX'     => 'N',
	"PATH"        => array(
		"ID"    => "Codenails",
		"SORT"  => 200,
		"NAME"  => Loc::GetMessage("CODENAILS_COMPONENTS"),
		"CHILD" => array(
			"ID"   => "cn_custom",
			"NAME" => Loc::GetMessage("CN_CUSTOM"),
			"SORT" => 10,
		),
	),
);