<?

use Bitrix\Main\Loader;
use Sotbit\Origami\Helper\Config;

global $USER, $APPLICATION;

$showFooter = false;


if ($_REQUEST['ajax_mode'] == 'Y') {
    require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

    if ($USER->GetID()) {
        echo '<script>setTimeout(function(){ location.reload(); }, 0);</script>';
    } else {

        //start
        $bSkip = false;
        if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
            $oManager = \BXmaker\AuthUserPhone\Manager::getInstance();
            //если модуль для текущего сайта включен
            if ($oManager->isEnabled()) {
                $bSkip = true;

                // подключение комопеннта
                $APPLICATION->IncludeComponent(
                    $oManager->param()->getDefaultComponent(),
                    '',
                    [
                        'COMPOSITE_FRAME_MODE' => 'N',
                        'RAND_STRING' => 'customAjax',
                    ]
                );
            }
        }

        if (!$bSkip) {
            $APPLICATION->AuthForm(
                '',
                false,
                false,
                'N',
                true
            );
        }
        //stop

//        закомментируем старый вызов
//        $APPLICATION->AuthForm(
//            '',
//            false,
//            false,
//            'N',
//            true
//        );

    }
    die;
} elseif ($_REQUEST['confirm_registration'] == 'yes' && intval($_REQUEST['confirm_user_id']) > 0) {

    // start
    define('NEED_AUTH', true);
    //stop

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
    $showFooter = true;
    $APPLICATION->AuthForm(
        '',
        false,
        false,
        'N',
        false
    );
} elseif (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    // start
    define('NEED_AUTH', true);
    //stop

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
    $showFooter = true;
    LocalRedirect('/');
}


CJSCore::Init(['phone_number']);
CJSCore::Init(["popup", "jquery"]);

?>
<? if (!$USER->IsAuthorized()): ?>
    <?
    Loader::includeModule('sotbit.origami');
    $telMask = \Sotbit\Origami\Config\Option::get('MASK', SITE_ID);
    $jsAuthVariable = 'fix' . \Bitrix\Main\Security\Random::getString(20);
    $APPLICATION->SetAdditionalCSS(SITE_DIR . "auth/style.css");
    ?><? if (Config::get('HEADER') == 1): ?>
        <a href="#" onclick="<?= $jsAuthVariable ?>.showPopup('/auth/')" rel="nofollow"><?= GetMessage('LOGIN') ?></a>
    <? elseif (Config::get('HEADER') == 5): ?>
        <a class="header-three__personal-link" href="#" onclick="<?= $jsAuthVariable ?>.showPopup('/auth/')"
           rel="nofollow">
            <div class="header-three__personal-photo"></div>
            <span> <?= GetMessage('LOGIN') ?></span>
        </a>
    <? else: ?>
        <a class="header-three__personal-link" href="#" onclick="<?= $jsAuthVariable ?>.showPopup('/auth/')"
           rel="nofollow">
            <div class="header-three__personal-photo header-three__personal-photo_custom-icon"></div>
            <?= GetMessage('LOGIN') ?>
        </a>
    <? endif; ?>
    <script>
        let <?=$jsAuthVariable?> = {
            id: "modal_auth",
            popup: null,
            convertLinks: function () {
                let links = $("#" + this.id + " a:not([id^=bx_socserv_icon])");
                links.each(function (i) {
                    $(this).attr('onclick', "<?=$jsAuthVariable?>.set('" + $(this).attr('href') + "')");
                });
                links.attr('href', '#');

                let form = $("#" + this.id + " form");
                form.attr('onsubmit', "<?=$jsAuthVariable?>.submit('" + form.attr('action') + "');return false;");
            },

            runScripts: function (arr) {
                arr.forEach((item) => {
                    BX.evalGlobal(item.JS)
                });
            },

            showPopup: function (url) {
                let app = this;
                let content = this.getForm(url);
                this.popup = BX.PopupWindowManager.create(this.id, '', {
                    closeIcon: true,
                    autoHide: true,
                    draggable: {
                        restrict: true
                    },
                    closeByEsc: true,
                    content: content.html,
                    overlay: {
                        backgroundColor: 'black',
                        opacity: '20'
                    },
                    className: 'auth-popup',
                    events: {
                        onPopupClose: function (PopupWindow) {
                            PopupWindow.destroy();
                        },
                        onAfterPopupShow: function (PopupWindow) {
                            app.convertLinks();
                        }
                    }
                });

                this.popup.show();

                const popupWrapper = document.querySelector('#modal_auth');
                popupWrapper.style.position = 'fixed';
                popupWrapper.style.zIndex = '1050';
                popupWrapper.style.top = '50%';
                popupWrapper.style.left = '50%';
                popupWrapper.style.transform = 'translate(-50%, -50%)';

                window.addEventListener('resize', () => {
                    const popupWrapper = document.querySelector('#modal_auth');
                    if (!popupWrapper) {
                        return;
                    }

                    popupWrapper.style.top = '50%';
                    popupWrapper.style.left = '50%';
                })

                try {
                    const scripts = content.JS;
                    this.runScripts(scripts);
                    setClassInputFilled();
                } catch (error) {
                    console.warn(error);
                }

            },

            getForm: function (url) {
                let content = null;
                url += (url.includes("?") ? '&' : '?') + 'ajax_mode=Y';
                BX.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'html',
                    async: false,
                    preparePost: false,
                    start: true,
                    processData: false,
                    skipAuthCheck: true,
                    onsuccess: function (data) {
                        content = {
                            html: BX.processHTML(data).HTML,
                            JS: BX.processHTML(data).SCRIPT
                        }
                    },
                    onfailure: function (html, e) {
                        console.error('getForm onfailure html', html, e, this);
                    }
                });

                return content;
            },

            set: function (url) {
                let form = this.getForm(url);
                this.popup.setContent(form.html);
                // this.popup.adjustPosition();
                this.convertLinks();
                try {
                    const scripts = form.JS;
                    this.runScripts(scripts);
                    setClassInputFilled();
                } catch (error) {
                    console.warn(error);
                }
                if (document.querySelector('.js-phone')) {
                    $(document).ready(function () {
                        $('.js-phone').inputmask("<?= $telMask ?>");
                    });
                }
            },
            /**
             * пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅпїЅпїЅ
             * @param url - url пїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ
             */
            submit: function (url) {
                let app = this;
                let form = document.querySelector("#" + this.id + " form");
                let data = BX.ajax.prepareForm(form).data;
                data.ajax_mode = 'Y';

                BX.ajax({
                    url: url,
                    data: data,
                    method: 'POST',
                    preparePost: true,
                    dataType: 'html',
                    async: false,
                    start: true,
                    processData: true,
                    skipAuthCheck: true,
                    onsuccess: function (data) {
                        let html = BX.processHTML(data);
                        app.popup.setContent(html.HTML);
                        app.convertLinks();
                    },
                    onfailure: function (html, e) {
                        console.error('getForm onfailure html', html, e, this);
                    }
                });
            }
        };
    </script>
<? endif; ?>
<? if ($showFooter) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
} ?>
