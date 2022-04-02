<?
// содержимое файла /ajax/auth.php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

global $USER;

if ($_GET['auth_service_error']) {
    LocalRedirect(SITE_DIR . 'personal/');
}

/**
 * @var $component \CBitrixComponent
 * @var $APPLICATION \CMain
 */

if (!$USER->IsAuthorized()): ?>
    <script src="<?= SITE_TEMPLATE_PATH . '/js/phoneorlogin.min.js'; ?>"></script>
    <?
    if (isset($_REQUEST['backurl']) && $_REQUEST['backurl']) {
        // fix ajax url
        if ($_REQUEST['backurl'] != $_SERVER['REQUEST_URI']) {
            $_SERVER['QUERY_STRING'] = '';
            $_SERVER['REQUEST_URI'] = $_REQUEST['backurl'];
            $APPLICATION->reinitPath();
        }
    } ?>
    <a href="#" class="close jqmClose"><?= CNext::showIconSvg('', SITE_TEMPLATE_PATH . '/images/svg/Close.svg') ?></a>

    <div id="wrap_ajax_auth" class="form">
        <?
        $bSkip = false;

        if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {

            $oManager = \Bxmaker\AuthUserPhone\Manager::getInstance();

            //если модуль для текущего сайта включен
            if ($oManager->isEnabled()) {

                $bSkip = true;


                // подклчюение расширения необходимого для работы компонента в публичнйо части
                \Bitrix\Main\UI\Extension::load('bxmaker.authuserphone.simple');
                echo \CJSCore::GetHTML(['bxmaker.authuserphone.simple']);


                // подключение комопеннта
                $APPLICATION->IncludeComponent(
                    'bxmaker:authuserphone.simple',
                    '',
                    [
                        'COMPOSITE_FRAME_MODE' => 'N'
                    ]
                );


                // стили чтобы попап окно было соразмерно контенту компонента
                ?>
                <style>
                    .auth_frame.popup {
                        width: auto;
                        max-width: 375px;
                        min-width: 375px;
                    }

                    .auth_frame.popup .close {
                        right: 25px;
                        top: 20px;
                    }
                </style>
                <?
            }
        }

        // если модуль для текущего сайта не включен или вообще не установлен, показываем исходный вариант
        if (!$bSkip) {
            ?>
            <div class="form_head">
                <h2><?= \Bitrix\Main\Localization\Loc::getMessage('AUTHORIZE_TITLE'); ?></h2>
            </div>

            <?
            $APPLICATION->IncludeComponent(
                "bitrix:system.auth.form",
                "main",
                [
                    "REGISTER_URL" => SITE_DIR . "auth/registration/?register=yes",
                    "PROFILE_URL" => SITE_DIR . "auth/",
                    "FORGOT_PASSWORD_URL" => SITE_DIR . "auth/forgot-password/?forgot-password=yes",
                    "AUTH_URL" => SITE_DIR . "auth/",
                    "SHOW_ERRORS" => "Y",
                    "POPUP_AUTH" => "Y",
                    "AJAX_MODE" => "Y",
                    "BACKURL" => ((isset($_REQUEST['backurl']) && $_REQUEST['backurl']) ? $_REQUEST['backurl'] : "")
                ]
            );
        }
        ?>

    </div>
<?
elseif (strlen($_REQUEST['backurl'])): ?>
    <?
    LocalRedirect($_REQUEST['backurl']); ?>
<?
else: ?>
    <?
    if (strpos($_SERVER['HTTP_REFERER'], SITE_DIR . 'personal/') === false && strpos($_SERVER['HTTP_REFERER'], SITE_DIR . 'ajax/form.php') === false): ?>
        $APPLICATION->ShowHead();
        ?>
        <script>
            jsAjaxUtil.ShowLocalWaitWindow('id', 'wrap_ajax_auth', true);
            BX.reload(false)
        </script>
    <?
    else: ?>
        <?
        LocalRedirect(SITE_DIR . 'personal/'); ?>
    <?
    endif; ?>
<?
endif; ?>