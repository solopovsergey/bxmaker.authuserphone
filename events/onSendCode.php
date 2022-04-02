<?php


$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "onSendCode",
    "bxmaker_authuserphone_onSendCode"
);


function bxmaker_authuserphone_onSendCode(\Bitrix\Main\Event $event)
{
    $arParams = $event->getParameters();

    // выбросим исключение, текст которого отобразитьс€ в публичной части
    if ($arParams['PHONE'] == '79991112233') {
        throw  new \Bxmaker\AuthUserPhone\Exception\BaseException(
            'Ќа ваш номер запрещено отправл€ть временные коды',
            'ERROR_INVALID_PHONE'
        );
    }

    // или вернем ошибку, чтобы была произведена попытка
    // отправить код через встроенные —ћ— —ервисис битркиса
    if ($arParams['PHONE'] == '79991112244') {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::ERROR,
            new \Bitrix\Main\Error(
                'Ќе удалось отправить смс через свой сервис, он недоступен пока'
            )
        );
    }

    //дополним массив парамтеров какими то данными или заменим
    //  больше полезно  в других событи€х, н
    //апрмер при старте отправки кода в смс
    if ($arParams['PHONE'] == '79991112255') {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                'TEST' => 1212
            ]
        );
    }

    //иначе все ок, не возращаем никакие данные
    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS,
        null
    );

}