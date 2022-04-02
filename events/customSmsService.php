<?php

// Пример добавления обработчиков модуля для отправки смс кодов и паролей через не поддерживаемый модулем смс сервис

//Код можно разместить в файле `/bitrix/php_interface/init.php`

// регистрируем обработчики событий
AddEventHandler('bxmaker.authuserphone', 'onSendCode', ['CBXmakerTools', 'authUserPhoneonSendCode']);
AddEventHandler('bxmaker.authuserphone', 'onUserChangePassword', ['CBXmakerTools', 'authUserPhoneonUserChangePassword']);
AddEventHandler('bxmaker.authuserphone', 'onUserAdd', ['CBXmakerTools', 'authUserPhoneUserAdde']);


class CBXmakerTools
{

    private static $smsLogin = '**';
    private static $smsPass = '****';
    private static $smsSender = '***';

    /**
     * Отправка временного кода
     * @param $arFields
     */
    public static function authUserPhoneonSendCode($arFields)
    {
        if (self::sendSms($arFields['PHONE'], 'Ваш временный код - ' . $arFields['CODE'])) {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                null,
            );
        } else {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::ERROR,
                new \Bitrix\Main\Error('Eroror send sms', 'ERROR_SMS_SEND')
            );
        }

    }

    /* после смены пароля */
    public static function authUserPhoneonUserChangePassword($arFields)
    {
        if (self::sendSms($arFields['PHONE'], 'Ваш новый пароль - ' . $arFields['PASSWORD'])) {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                null,
            );
        } else {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::ERROR,
                new \Bitrix\Main\Error('Eroror send sms', 'ERROR_SMS_SEND')
            );
        }
    }

    /* после регистрации */
    public static function authUserPhoneUserAdde($arFields)
    {
        if (self::sendSms($arFields['PHONE'], 'Вы успешно зарегистрированы на сайте, используйте для входа логин - ' . $arFields['PHONE'] . ' и пароль - ' . $arFields['PASSWORD'])) {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS,
                null
            );
        } else {
            return new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::ERROR,
                new \Bitrix\Main\Error('Eroror send sms', 'ERROR_SMS_SEND')
            );
        }
    }


    public static function sendSms($phone, $text)
    {

        $arFields = [
            'user' => self::$smsLogin,
            'password' => self::$smsPass,
            'recipient' => $phone,
            'message' => \Bitrix\Main\Text\Encoding::convertEncoding($text, SITE_CHARSET, 'UTF-8')
        ];

        if (self::$smsSender) {
            $arFields['sender'] = self::$smsSender;
        }

        $oHttp = new \Bitrix\Main\Web\HttpClient();
        $oHttp->post('https://example.com/api', $arFields); // вместо * - нужно указать адрес API смс сервиса  на которые нужно отправлять запросы

        return true;
    }

}