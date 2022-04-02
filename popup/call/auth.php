<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

/**
 * @var $APPLICATION \CMain
 */


$APPLICATION->IncludeComponent(
    'bxmaker:authuserphone.call',
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

            $('.bxmaker-authuserphone-call.inited').removeClass('inited');

            window.BxmakerAuthUserphoneCall = [];


            BxmakerAuthUserphoneCallWorker();

            // здесь же можно налозить маску на поля
            var mask = '7 (999) 999-99-99';

            // следующая инструкция позволяет использоваться масску из настроек на решениях от Аспро
            if (!!window.arAsproOptions && !!arAsproOptions['THEME'] && !!arAsproOptions['THEME']['PHONE_MASK']) {
                mask = arAsproOptions['THEME']['PHONE_MASK'];
            }

            var makParams = {
                mask: mask,
                showMaskOnHover: false,
            };

            $('input[name=phone]').inputmask('mask', makParams);
            $('input[name=register_phone]').inputmask('mask', makParams);
            $('input[name=forget_phone]').inputmask('mask', makParams);

        }, 600);


    </script>


<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
?>