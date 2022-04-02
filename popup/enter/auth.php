<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

/**
 * @var $APPLICATION \CMain
 */

// подклчюение расширения необходимого для работы компонента в публичнйо части
\Bitrix\Main\UI\Extension::load('bxmaker.authuserphone.enter');
echo \CJSCore::GetHTML(['bxmaker.authuserphone.enter']);


// подключение комопеннта
$APPLICATION->IncludeComponent(
    'bxmaker:authuserphone.enter',
    '',
    [
        'COMPOSITE_FRAME_MODE' => 'N'
    ]
);


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>