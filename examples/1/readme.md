# В регистрацию добавить поле

- Решение - Аспро Макс
- Компонент - Enter

Нужно для формы регистрации добавить поле для ввода карты лояльности.
Добавить чекбокс - У меня нет карты , при активации которого поле с картой
скрывается и является не обязательным.

Добавляем обработчики событий для проверки заполненности
поля карта лояльности либо наличие галочки - "У меня нет карты"

## init.php

```php
//  /bitrix/php_interface/init.php
if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {

    $eventManager = \Bitrix\Main\EventManager::getInstance();
    // проверка заполнености поля карта лояльности или стоит галочка у меня нет краты
    $eventManager->addEventHandler(
        "bxmaker.authuserphone",
        "BXmakerAuthUserPhoneEnterComponentAjax",
        "bxmaker_authuserphone_loyality_card_reg"
    );
    // проверка краты или галочки перед регистрацией
    $eventManager->addEventHandler(
        "main",
        "OnBeforeUserRegister",
        "bxmaker_authuserphone_event_main_onBeforeUserRegister"
    );
   
    function bxmaker_authuserphone_loyality_card_reg(\Bitrix\Main\Event $event)
    {
        /**
         * @var $jsonResponse \BXmaker\AuthUserPhone\Ajax\JsonResponse
         * @var $component \BXmakerAuthUserPhoneCallComponent
         */
        $fields = $event->getParameter('fields');
        $jsonResponse = $fields['jsonResponse'];
        $component = $fields['component'];


        $actionType = $component->request()->getPost('actionType');
        if ($actionType !== 'REG') {
            return;
        }

        // дополнитлеьные данные приходят в этом поле
        $arExpandData = (array) $component->request()->getPost('expandData');
        if($arExpandData['hasNotCard'] === 'true')
        {
            return;
        }

        $kartaM = preg_replace('/[^\d]+/', '', $arExpandData['ufKartaM'] ?? '');

        if (empty($kartaM)) {
            throw new \BXmaker\AuthUserPhone\Exception\BaseException(
                'Не заполнено обязательное поле - Карта лояльности', 'ERROR_USER_FIELD_UF_KARTA_M'
            );
        }
    }


    function bxmaker_authuserphone_event_main_onBeforeUserRegister(&$arFields)
    {
        $req = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        // дополнитлеьные данные приходят в этом поле
        $arExpandData = $req->getPost('expandData');


        if(!is_array($arExpandData)
            || !array_key_exists('ufKartaM', $arExpandData)
            || !array_key_exists('hasNotCard', $arExpandData)
        )
        {
            return ;
        }

        if($arExpandData['hasNotCard'] === 'true')
        {
            return;
        }

        $kartaM = preg_replace('/[^\d]+/', '', $arExpandData['ufKartaM']);

        if (empty($kartaM)) {
            throw new \BXmaker\AuthUserPhone\Exception\BaseException(
                'Не заполнено обязательное поле - Карта лояльности', 'ERROR_USER_FIELD_UF_KARTA_M'
            );
        }

        $arFields['UF_KARTA_M'][] = $kartaM;
    }
    
}
```

## Языковые фразы для мутации Vue комопнента
Чтобы выводились подсказки в доп полям, добавляем в файл **/bitrix/php_interface/user_lang/ru/lang.php**
```php
//...
// bxmaker.authuserphone
$MESS["/bitrix/js/bxmaker/authuserphone/enter/lang/ru/config.php"]["BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__UF_KARTA_M"] = "Карта лояльности ";
$MESS["/bitrix/js/bxmaker/authuserphone/enter/lang/ru/config.php"]["BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__HAS_NOT_CARD"] = "У меня нет карты";
```

##  Мутация Vue компонента
Есть два вараинт аподклчюения
1. Когда форма отображается сразу
2. когда форма загружается по ajax

### Форма отображается сразу
Мутация должна подклчюаться на странице вместе с шаблоном чтобы применилась мутация. Для этого можно в файл
**/include/header_include/head_custom.php**
```js
<script>
if (BX.Vue) {
    BX.ready(function () {
        BX.Vue.mutateComponent('BXmakerAuthuserphoneEnterRegForm', {
            name: 'BXmakerAuthuserphoneEnterRegForm',
            data() {
                return {
                    ufKartaM: '',
                    hasNotCard: false,
                };
            },
            created() {
                this.$set(this.$root.expandData, 'ufKartaM', '');
                this.$set(this.$root.expandData, 'hasNotCard', false);
            },
            watch: {
                hasNotCard(val) {
                    this.$set(this.$root.expandData, 'hasNotCard', val);
                }
            },
            methods: {
                onChangeUfKartaM(value) {
                    this.ufKartaM = value;
                    this.$set(this.$root.expandData, 'ufKartaM', value);
                },
               
            },
            template: `
            <div class="bxmaker-authuserphone-enter-reg-form"  >
                 
                 <slot name="message" />  
                        <BXmakerAuthuserphoneMessage :message="$root.message" :error="$root.error" />
                   </slot>     
                   
                  
                   <BXmakerAuthuserphoneInput
                            v-if="!hasNotCard"
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__UF_KARTA_M" 
                            :value="ufKartaM" 
                            @onInput="onChangeUfKartaM"              
                            name="UF_KARTA_M" 
                        />
                      
                                       
                <label class="bxmaker-authuserphone-enter-reg-form-hasNotCard" 
                for="bxmaker-authuserphone-enter-reg-form-hasNotCard__input"
                style="position: relative; top: -12px;font-weight:normal;"
                 >
                    <input id="bxmaker-authuserphone-enter-reg-form-hasNotCard__input" type="checkbox" value="1" v-model="hasNotCard" />
                    <span>{{$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__HAS_NOT_CARD}}</span>
                </label>

                     <slot name="login" v-if="$root.isEnabledRegisterFIO">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_FIO" 
                            :value="$root.fio" 
                            @onInput="$root.setFIO" 
                            @onEnter="onEnterFIO"   
                            name="FIO" 
                            ref="fio"
                        />
                    </slot>            
                                      
                    <slot name="phone">        
                    
                      <BXmakerAuthuserphoneInputPhone  v-if="$root.isEnabledPhoneMask"
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PHONE" 
                            :value="$root.phone" 
                            @onChange="onChangePhone" 
                            @onEnter="onEnterPhone" 
                            name="PHONE" 
                            :defaultCountry="$root.phoneMaskDefaultCountry"
                            :countryTopList="$root.phoneMaskCountryTopList"                          
                        />
                                  
                        <BXmakerAuthuserphoneInput v-else 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PHONE" 
                            :value="$root.phone" 
                            @onInput="$root.setPhone"        
                            @onEnter="onEnterPhone"                    
                            name="PHONE" 
                        />
                    </slot>
                    <slot name="login" v-if="$root.isEnabledRegisterLogin">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_LOGIN" 
                            :value="$root.login" 
                            @onInput="$root.setLogin" 
                            @onEnter="onEnterLogin"   
                            name="LOGIN" 
                            ref="login"
                        />
                    </slot>
                    <slot name="email" v-if="$root.isEnabledRegisterEmail">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_EMAIL" 
                            :value="$root.email" 
                            @onInput="$root.setEmail" 
                            @onEnter="onEnterEmail"   
                            name="EMAIL" 
                            ref="email"
                        />
                    </slot>
                    
                     <slot name="pass" v-if="$root.isEnabledRegisterPassword">                   
                        <BXmakerAuthuserphoneInputPassword 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PASSWORD" 
                            :value="$root.password" 
                            @onInput="$root.setPassword" 
                            @onEnter="onEnterPassword"   
                            name="PASSWORD" 
                            ref="password"
                        />
                    </slot>
                                                            
                    
                 <slot name="captcha">
                        <BXmakerAuthuserphoneCaptcha 
                            :code="$root.captchaCode"
                            :src="$root.captchaSrc"
                            :length="$root.captchaLength"
                            :loader="$root.captchaLoader"
                            @onInput="$root.setCaptchaCode"
                            @onComplete="onClickConfirm"
                            @onRefresh="$root.refreshCaptcha"
                           />  
                 </slot>   
                 
                 <slot name="consent" v-if="$root.isEnabledRequestConsent">
                    <BXmakerAuthuserphoneConsent
                        :button="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_BUTTON"
                        :text="$root.consentText"
                        :isReceived="$root.isConsentReceived"
                        @onAgree="onConsentAgree"
                        @onDisagree="$root.consentDisagree"
                        ref="consent"
                     />
                 </slot>   
                 
                     
                     <slot name="request">
                        <BXmakerAuthuserphoneButton 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_BUTTON"  
                            :loader="$root.startLoader"
                            @onClick="onClickConfirm" 
                        />
                    </slot>
                   
                             
            </div>
        `,
        });
    });
}
</script>
```

###  Форма подгружается по ajax
В этом случае необходимо в файле обработчике ajax запроса к отдаче формы комопнента добавить еще и скрипт, 
который произведе мутацию. **Важно**: чтобы мутация сработала, нужно убедиться что на странице подключается библиотека 
`ui.vue`. Иначе мутация не применится. В код ниже сразу добавлено подклчюение библитеки
на случае если библиотек ана странцие еще не подклчюена.

```php
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


if (!$USER->IsAuthorized()) : ?>
    <script src="<?= SITE_TEMPLATE_PATH . '/js/phoneorlogin.min.js' ?>"></script>
    <?
    if (isset($_REQUEST['backurl']) && $_REQUEST['backurl']) {
        // fix ajax url
        if ($_REQUEST['backurl'] != $_SERVER['REQUEST_URI']) {
            $_SERVER['QUERY_STRING'] = '';
            $_SERVER['REQUEST_URI'] = $_REQUEST['backurl'];
            //$APPLICATION->reinitPath();
            $APPLICATION->sDocPath2 = GetPagePath(false, true);
            $APPLICATION->sDirPath = GetDirPath($APPLICATION->sDocPath2);
        }
    } ?>
    <a href="#" class="close jqmClose"><?= CMax::showIconSvg('', SITE_TEMPLATE_PATH . '/images/svg/Close.svg') ?></a>

    <div id="wrap_ajax_auth" class="form">
        <?

        $bSkip = false;

        //подклчюаем модуль
        if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {

// подключение библитеки необходимой
\Bitrix\Main\UI\Extension::load('ui.vue');
echo \CJSCore::GetHTML(['ui.vue']);
//хххххх


            $oManager = \BXmaker\AuthUserPhone\Manager::getInstance();

            //если модуль для текущего сайта включен
            if ($oManager->isEnabled()) {

                $bSkip = true;

                // подключение комопеннта
                $APPLICATION->IncludeComponent(
                    $oManager->param()->getDefaultComponent(),
                    //"bxmaker:authuserphone.login",
                    "",
                    [
                        'COMPOSITE_FRAME_MODE' => 'N',
                    ]
                );


                // стили чтобы попап окно было соразмерно контенту компонента
                // добавление мутации
        ?>
                <style>
                    .auth_frame.popup {
                        width: auto;
                    }
                </style>

        <script>
        BX.Vue.mutateComponent('BXmakerAuthuserphoneEnterRegForm', {
            name: 'BXmakerAuthuserphoneEnterRegForm',
            data() {
                return {
                    ufKartaM: '',
                    hasNotCard: false,
                };
            },
            created() {
                this.$set(this.$root.expandData, 'ufKartaM', '');
                this.$set(this.$root.expandData, 'hasNotCard', false);
            },
            watch: {
                hasNotCard(val) {
                    this.$set(this.$root.expandData, 'hasNotCard', val);
                }
            },
            methods: {
                onChangeUfKartaM(value) {
                    this.ufKartaM = value;
                    this.$set(this.$root.expandData, 'ufKartaM', value);
                },
               
            },
            template: `
            <div class="bxmaker-authuserphone-enter-reg-form"  >
                 
                 <slot name="message" />  
                        <BXmakerAuthuserphoneMessage :message="$root.message" :error="$root.error" />
                   </slot>     
                   
                  
                   <BXmakerAuthuserphoneInput
                            v-if="!hasNotCard"
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__UF_KARTA_M" 
                            :value="ufKartaM" 
                            @onInput="onChangeUfKartaM"              
                            name="UF_KARTA_M" 
                        />
                      
                                       
                <label class="bxmaker-authuserphone-enter-reg-form-hasNotCard" 
                for="bxmaker-authuserphone-enter-reg-form-hasNotCard__input"
                style="position: relative; top: -12px;font-weight:normal;"
                 >
                    <input id="bxmaker-authuserphone-enter-reg-form-hasNotCard__input" type="checkbox" value="1" v-model="hasNotCard" />
                    <span>{{$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM__HAS_NOT_CARD}}</span>
                </label>
                            
                    <slot name="login" v-if="$root.isEnabledRegisterFIO">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_FIO" 
                            :value="$root.fio" 
                            @onInput="$root.setFIO" 
                            @onEnter="onEnterFIO"   
                            name="FIO" 
                            ref="fio"
                        />
                    </slot>            
                              
                    <slot name="phone">        
                    
                      <BXmakerAuthuserphoneInputPhone  v-if="$root.isEnabledPhoneMask"
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PHONE" 
                            :value="$root.phone" 
                            @onChange="onChangePhone" 
                            @onEnter="onEnterPhone" 
                            name="PHONE" 
                            :defaultCountry="$root.phoneMaskDefaultCountry"
                            :countryTopList="$root.phoneMaskCountryTopList"                          
                        />
                                  
                        <BXmakerAuthuserphoneInput v-else 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PHONE" 
                            :value="$root.phone" 
                            @onInput="$root.setPhone"        
                            @onEnter="onEnterPhone"                    
                            name="PHONE" 
                        />
                    </slot>
                    <slot name="login" v-if="$root.isEnabledRegisterLogin">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_LOGIN" 
                            :value="$root.login" 
                            @onInput="$root.setLogin" 
                            @onEnter="onEnterLogin"   
                            name="LOGIN" 
                            ref="login"
                        />
                    </slot>
                    <slot name="email" v-if="$root.isEnabledRegisterEmail">                   
                        <BXmakerAuthuserphoneInput 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_EMAIL" 
                            :value="$root.email" 
                            @onInput="$root.setEmail" 
                            @onEnter="onEnterEmail"   
                            name="EMAIL" 
                            ref="email"
                        />
                    </slot>
                    
                     <slot name="pass" v-if="$root.isEnabledRegisterPassword">                   
                        <BXmakerAuthuserphoneInputPassword 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_PASSWORD" 
                            :value="$root.password" 
                            @onInput="$root.setPassword" 
                            @onEnter="onEnterPassword"   
                            name="PASSWORD" 
                            ref="password"
                        />
                    </slot>
                                                            
                    
                 <slot name="captcha">
                        <BXmakerAuthuserphoneCaptcha 
                            :code="$root.captchaCode"
                            :src="$root.captchaSrc"
                            :length="$root.captchaLength"
                            :loader="$root.captchaLoader"
                            @onInput="$root.setCaptchaCode"
                            @onComplete="onClickConfirm"
                            @onRefresh="$root.refreshCaptcha"
                           />  
                 </slot>   
                 
                 <slot name="consent" v-if="$root.isEnabledRequestConsent">
                    <BXmakerAuthuserphoneConsent
                        :button="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_BUTTON"
                        :text="$root.consentText"
                        :isReceived="$root.isConsentReceived"
                        @onAgree="onConsentAgree"
                        @onDisagree="$root.consentDisagree"
                        ref="consent"
                     />
                 </slot>   
                 
                     
                     <slot name="request">
                        <BXmakerAuthuserphoneButton 
                            :title="$root.localize.BXMAKER_AUTHUSERPHONE_ENTER_REG_FORM_BUTTON"  
                            :loader="$root.startLoader"
                            @onClick="onClickConfirm" 
                        />
                    </slot>
                   
                             
            </div>
        `,
        });

		</script>
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
        <?/*<div class="bxmaker-authuserphone-enter-auth__toregistration">
			<a href="/auth/registration/" class="bxmaker-authuserphone-link">
			   Регистрация Юридических лиц 
			</a>
		</div>
		<br>*/ ?>
    </div>
<?
elseif (strlen($_REQUEST['backurl'])) : ?>
    <?
    LocalRedirect($_REQUEST['backurl']); ?>
<?
else : ?>
    <?
    if (strpos($_SERVER['HTTP_REFERER'], SITE_DIR . 'personal/') === false && strpos($_SERVER['HTTP_REFERER'], SITE_DIR . 'ajax/form.php') === false) : ?>
        $APPLICATION->ShowHead();
        ?>
        <script>
            jsAjaxUtil.ShowLocalWaitWindow('id', 'wrap_ajax_auth', true);
            BX.reload(false)
        </script>
    <?
    else : ?>
        <?
        LocalRedirect(SITE_DIR . 'personal/'); ?>
    <?
    endif; ?>
<?
endif; ?>
```


