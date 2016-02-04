<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule("iblock")) {
	return;
}

$arTypesEx = array();

if ($arCurrentValues["USE_IBLOCK"] == 'Y') {
	$db_iblock_type = CIBlockType::GetList(array("SORT" => "ASC"));
	while ($arRes = $db_iblock_type->Fetch()) {
		if ($arIBType = CIBlockType::GetByIDLang($arRes["ID"], LANG)) {
			$arTypesEx[$arRes["ID"]] = $arIBType["NAME"];
		}
	}
}
$arIBlocks = array();

if ($arCurrentValues["USE_IBLOCK"] == 'Y') {
	$db_iblock = CIBlock::GetList(array("SORT" => "ASC"), array("SITE_ID" => $_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"] != "-" ? $arCurrentValues["IBLOCK_TYPE"] : "")));
	while ($arRes = $db_iblock->Fetch()) {
		$arIBlocks[$arRes["ID"]] = $arRes["NAME"];
	}
}

$arSections = array();
if (intval($arCurrentValues["IBLOCK_ID"]) > 0) {
	$dbSect = CIBlockSection::GetList(array('left_margin' => 'asc'), array('IBLOCK_ID' => $arCurrentValues["IBLOCK_ID"]));
	while ($arSect = $dbSect->Fetch()) {
		$arSections[$arSect['ID']] = '[' . $arSect['ID'] . ']' . str_repeat('.', $arSect['DEPTH_LEVEL']) . ' ' . $arSect['NAME'];
	}
}

$arEvents = array();
if ($arCurrentValues["SEND_NOTIFICATION"] == 'Y') {
	$dbEvent = CEventType::GetList(array('LID' => SITE_ID), array('TYPE_ID' => 'ASC'));
	while ($arEvent = $dbEvent->Fetch()) {
		$arEvents[$arEvent['EVENT_NAME']] = $arEvent['NAME'];
	}
}

$arGroups = array();
$dbGroup  = CGroup::GetList(($by = "c_sort"), ($order = "desc"));
while ($arGroup = $dbGroup->Fetch()) {
	$arGroups[$arGroup['ID']] = '[' . $arGroup['ID'] . '] ' . $arGroup['NAME'];
}

$arComponentParameters = array(
	"GROUPS"     => array(

	),
	"PARAMETERS" => array(
		"FORM_CODE"              => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_FORM_CODE_PARAM_TITLE"),
			"TYPE"    => "STRING",
			"DEFAULT" => "custom",
		),
		"USE_IBLOCK"             => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_USE_IBLOCK_PARAM_TITLE"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"IBLOCK_TYPE"            => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("T_IBLOCK_DESC_LIST_TYPE"),
			"TYPE"    => "LIST",
			"VALUES"  => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID"              => array(
			"PARENT"   => "BASE",
			"NAME"     => Loc::GetMessage("T_IBLOCK_DESC_LIST_ID"),
			"TYPE"     => "LIST",
			"VALUES"   => $arIBlocks,
			"DEFAULT"  => '',
			"MULTIPLE" => "N",
			"REFRESH"  => "Y",
		),
		"PARENT_SECTION_ID"      => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_PARENT_SECTION_ID_PARAM_TITLE"),
			"TYPE"    => "LIST",
			"DEFAULT" => "0",
			"VALUES"  => $arSections,
		),
		"ACTIVE_ITEM"            => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_ACTIVE_ITEM"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"AJAX_OPEN_FORM"         => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_AJAX_OPEN_FORM_PARAM_TITLE"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"ENABLE_FAKE_FORM"       => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_ENABLE_FAKE_FORM_PARAM_TITLE"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SEND_NOTIFICATION"      => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_SEND_NOTIFICATION_PARAM_TITLE"),
			"TYPE"    => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"EVENT_NAME"             => array(
			"PARENT"  => "BASE",
			"NAME"    => Loc::GetMessage("CN_EVENT_NAME_PARAM_TITLE"),
			"TYPE"    => "LIST",
			"DEFAULT" => "",
			"VALUES"  => $arEvents,
		),
		"SHOW_FOR_USER_GROUPS"   => array(
			"PARENT"   => "ACCESS",
			"NAME"     => Loc::GetMessage("CN_SHOW_FOR_USER_GROUPS_PARAM_TITLE"),
			"TYPE"     => "LIST",
			"VALUES"   => $arGroups,
			"DEFAULT"  => '',
			"MULTIPLE" => "Y",
		),
		"ENABLE_FOR_USER_GROUPS" => array(
			"PARENT"   => "ACCESS",
			"NAME"     => Loc::GetMessage("CN_ENABLE_FOR_USER_GROUPS_PARAM_TITLE"),
			"TYPE"     => "LIST",
			"VALUES"   => $arGroups,
			"DEFAULT"  => '',
			"MULTIPLE" => "Y",
		),
		"NOT_ENABLE_NOTE"        => array(
			"PARENT"  => "ACCESS",
			"NAME"    => Loc::GetMessage("CN_NOT_ENABLE_NOTE_PARAM_TITLE"),
			"TYPE"    => "STRING",
			"ROWS"    => 3,
			"DEFAULT" => "",
		),
		'CACHE_TIME'             => array(),
	),
);