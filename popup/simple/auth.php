<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// файл обработчик ajax запрос, который вернет html, стили, скрипты для показа авторизации в попап окне

/**
 * @var $APPLICATION \CMain
 */

// подключение комопеннта
$APPLICATION->IncludeComponent(
    'bxmaker:authuserphone.simple',
    '',
    [
        'COMPOSITE_FRAME_MODE' => 'N'
    ]
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>