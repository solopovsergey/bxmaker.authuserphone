<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$this->SetFrameMode(true); ?>

<?php

// bitrix/templates/concept_phoenix_s2/components/bitrix/system.auth.form/auth/template.php

?>

<?
//if($arResult["FORM_TYPE"] == "login"):?>

<?
global $PHOENIX_TEMPLATE_ARRAY; ?>

<?
$picture = (strlen($PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORM_PIC"]["VALUE"])) ? true : false;
?>

    <div class="phx-modal-dialog" data-target="auth-modal-dialog">
        <div class="dialog-content">
            <a class="close-phx-modal-dialog" data-target="auth-modal-dialog"></a>

            <div class="auth-dialog-form <?= ($picture) ? "with-pic" : ""; ?>">

                <div class="row no-gutters">

                    <?
                    if ($picture): ?>

                        <div class="col-md-7 hidden-sm hidden-xs picture"
                             style="background-image: url(<?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORM_PIC"]["SETTINGS"]["SRC"] ?>);"></div>

                    <?
                    endif; ?>

                    <div class="<?= ($picture) ? "col-md-5" : ""; ?> col-12">

                        <?
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
                            ?>

                            <form class="form auth" action="#">

                                <div class="title-form main1">
                                    <?= $PHOENIX_TEMPLATE_ARRAY["MESS"]["PERSONAL_LOGIN_TITLE"] ?>
                                </div>

                                <? if (strlen($PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORM_AUTH_SUBTITLE"]["VALUE"])): ?>

                                    <div class="subtitle-form"><?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORM_AUTH_SUBTITLE"]["~VALUE"] ?></div>

                                <?endif; ?>

                                <div class="inputs-block">

                                    <div class="input">
                                        <div class="bg"></div>
                                        <span class="desc"><?= $PHOENIX_TEMPLATE_ARRAY["MESS"]["PERSONAL_LOGIN_INPUT"] ?></span>
                                        <input class='focus-anim require' name="auth-login" type="text" value=""/>
                                    </div>
                                    <div class="input">
                                        <div class="bg"></div>
                                        <span class="desc"><?= $PHOENIX_TEMPLATE_ARRAY["MESS"]["PERSONAL_PASSWORD_INPUT"] ?></span>
                                        <input class='focus-anim require' name="auth-password" type="password"/>
                                    </div>
                                    <div class="errors"></div>

                                    <div class="input-btn">
                                        <div class="load">
                                            <div class="xLoader form-preload">
                                                <div class="audio-wave">
                                                    <span></span><span></span><span></span><span></span><span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="button-def main-color big active <?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["DESIGN"]["ITEMS"]['BTN_VIEW']['VALUE'] ?> auth-submit"
                                                name="form-submit"
                                                type="button"><?= $PHOENIX_TEMPLATE_ARRAY["MESS"]["PERSONAL_BTN_ENTER"] ?></button>
                                    </div>

                                </div>

                                <div class="input txt-center">
                                    <a class="forgot"
                                       href="<?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORGOT_PASSWORD_URL"]["VALUE"] ?>"><span
                                                class="bord-bot"><?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["FORGOT_PASSWORD_URL"]["DESCRIPTION"] ?></span></a>
                                </div>

                                <?/*<div class="soc-enter">
                                <div class="soc-enter-title">
                                    <div class="soc-enter-line"></div>
                                    <div class="soc-enter-text"><?=$PHOENIX_TEMPLATE_ARRAY["MESS"]["PERSONAL_AUTH_FORM_SOC"]?></div>
                                </div>
                                <div class="soc-enter-items">
                                    <a href="#" class="soc-enter-item"></a>
                                    <a href="#" class="soc-enter-item"></a>
                                </div>
                            </div>*/ ?>
                            </form>

                            <div class="register row no-margin">
                                <div class="col-12">

                                    <a href="<?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["REGISTER_URL"]["VALUE"] ?>"><span
                                                class="bord-bot"><?= $PHOENIX_TEMPLATE_ARRAY["ITEMS"]["PERSONAL"]["ITEMS"]["REGISTER_URL"]["DESCRIPTION"] ?></span></a>

                                </div>

                            </div>
                            <?
                        }
                        ?>

                    </div>

                </div>

            </div>
        </div>
    </div>

<?
//endif;?>