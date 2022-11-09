<?php

/**
 * Пример обработчика события который отправит смс с временным кодом
 * через какой-то неподдерживаемый модулем сервис
 * больше примера в файла customSmsService
 */



$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "onSendCode",
    "bxmaker_authuserphone_onSendCode"
);


function bxmaker_authuserphone_onSendCode(\Bitrix\Main\Event $event)
{
    $fields = (array) $event->getParameter('fields');

    // выбросим исключение, текст которого отобразиться в публичной части
    if ($fields['PHONE'] == '79991112233') {
        throw  new \BXmaker\AuthUserPhone\Exception\BaseException(
            'На ваш номер запрещено отправлять временные коды',
            'ERROR_INVALID_PHONE'
        );
    }

    // или вернем ошибку, чтобы была произведена попытка
    // отправить код через встроенные СМС Сервисис битркиса
    if ($fields['PHONE'] == '79991112244') {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::ERROR,
            new \Bitrix\Main\Error(
                'Не удалось отправить смс через свой сервис, он недоступен пока'
            )
        );
    }

    //дополним массив парамтеров какими то данными или заменим
    //  больше полезно  в других событиях, н
    //апрмер при старте отправки кода в смс
    if ($fields['PHONE'] == '79991112255') {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                'TEST' => 1212
            ]
        );
    }

    //отправляем код в смс
    // customSmsSend($fields['PHONE'], 'Временный код - ' . $fields['CODE"]);


    //иначе все ок, не возращаем никакие данные
    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS,
        null
    );

}
