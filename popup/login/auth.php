<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

// файл обработчик ajax запрос, который вернет html, стили, скрипты для показа авторизации в попап окне

/**
 * @var $APPLICATION \CMain
 */


$APPLICATION->IncludeComponent(
    'bxmaker:authuserphone.login',
    '',
    [
        'COMPOSITE_FRAME_MODE' => 'N'
    ]
);


//это нужно обязательно чтобы подгрузились стили и срипты, языковые фразы
echo \Bitrix\Main\Page\Asset::getInstance()->getCss();
echo \Bitrix\Main\Page\Asset::getInstance()->getJs(\Bitrix\Main\Page\AssetShowTargetType::TEMPLATE_PAGE);
echo \Bitrix\Main\Page\Asset::getInstance()->getStrings(\Bitrix\Main\Page\AssetLocation::AFTER_CSS);

?>


    <script type="text/javascript">

        //запускаем с маленькой задержкой
        setTimeout(function () {

            $('.bxmaker-authuserphone-login').each(function () {
                new BxmakerAuthUserphone(jQuery(this), jQuery);
            });

            // здесь же можно налозить маску на поля
            var mask = '7 (999) 999-99-99';

            // следующая инструкция позволяет использоваться масску из настроек на решениях от Аспро
            if (!!window.arAsproOptions && !!arAsproOptions['THEME'] && !!arAsproOptions['THEME']['PHONE_MASK']) {
                mask = arAsproOptions['THEME']['PHONE_MASK'];
            }

            var maskParams = {
                mask: mask,
                showMaskOnHover: false,
            };

            $('input[name=phone]').inputmask('mask', maskParams);
            $('input[name=register_phone]').inputmask('mask', maskParams);


        }, 600);


    </script>


<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>