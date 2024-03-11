# Добалвнеие поля дата рождения

В данном случае не приведен обработик на сервреной стороне, язвковые файлы и прочеее. Это лишь пример как добавить другое поле, прикрутить к нему маску.
Вся остальная логика похожа на 1 пример

> В данном случае код не адаптирован для старых браузеров, треубется  замена для шаблона апострофов на кавычки и другие преобразования, 
> методы дожны буть определены через function как для mount b beforeUnmount

```js
<script>

    // добавление поля даты рождения
if (BX.Vue) {
    BX.Vue.mutateComponent('BXmakerAuthuserphoneEnterRegForm', {
        name: 'BXmakerAuthuserphoneEnterRegForm',
        data() {
            return {
                personalBirthday: '',
            };
        },
        created() {
            this.$set(this.$root.expandData, 'personalBirthday', '');
        },
        // mounted: function () {
        //     var that = this;
        //     $(this.$refs.personalBirthday).inputmask('mask',
        //         {
        //             'mask': '99.99.9999',
        //             'onKeyUp': function (event) {
        //                 setTimeout(function () {
        //                     that.onChangeDateBirthday(event);
        //                 }, 0);
        //             }
        //         }
        //     );
        // },
        // beforeUnmount: function () {
        //     $(this.$refs.personalBirthday).inputmask('unmaskedvalue');
        // },
        methods: {
            onChangeDateBirthday(event) {
                var value = event.target.value;
                value = value.replace(/[^\d.]+/g, '');
                this.personalBirthday = value;
                this.$set(this.$root.expandData, 'personalBirthday', value);

            },
            onBeforeContinue() {
                if (this.personalBirthday.match(/^\d\d\.\d\d\.\d\d\d\d$/)) {
                    this.onClickConfirm();
                } else {
                    this.$root.setError('Wrong bithhday');
                }
            }
        },
        template: `
            <div class="bxmaker-authuserphone-enter-reg-form"  >
                 
                 <slot name="message" />  
                        <BXmakerAuthuserphoneMessage :message="$root.message" :error="$root.error" />
                   </slot>     
                                                        
                        
                    <!--ДАТА РОЖДЕНИЯ-->
                        <div class="bxmaker-authuserphone-input ">
                            <div class="bxmaker-authuserphone-input__field">
                                 <input
                                    type="text"
                                    class="select__option js-calendarDateRange"
                                    placeholder="Выбрать дату"
                                    @input="onChangeDateBirthday"
                                    :value="personalBirthday"
                                    name="PERSONAL_BIRTHDAY"
                                    ref="refBirthday"
                                    style="padding:12px 8px;"
                                 >
                            </div>
                        </div>                   
                    <!--ДАТА РОЖДЕНИЯ-->
                                                             
               
                            
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
                            @onComplete="onBeforeContinue"
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
                            @onClick="onBeforeContinue" 
                        />
                    </slot>
                   
                             
            </div>
        `,
    });
}

</script>
```
