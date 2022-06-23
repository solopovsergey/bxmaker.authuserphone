<?php

// пример обработчика события регистрации пользвоателя,
//  который дополнит поля пользователя дополнтельными данными
// Используется при расширении полей регистраций компонентов на vue


// разместить можно например в /bitrix/php_interface/init.php

if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {

    //регистрируем событие, которое также вызывает модуль перед добавлением пользователя
    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->addEventHandler(
        "main",
        "OnBeforeUserRegister",
        "bxmaker_authuserphone_event_main_onBeforeUserRegister"
    );


    // непосредственно обработчик осбытия
    function bxmaker_authuserphone_event_main_onBeforeUserRegister(&$arFields)
    {
        $req = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        // дополнитлеьные данные приходят в этом поле
        $arExpandData = $req->getPost('expandData');

        if (is_array($arExpandData) && isset($arExpandData['inviteCode'])) {
            $arFields['UF_CODE'] = trim(htmlentities($arExpandData['inviteCode'], ENT_QUOTES));
        }
        else
        {
            throw new \BXmaker\AuthUserPhone\Exception\BaseException('Не заполнено поле обязательное - Код приглашения', 'ERROR_INVITE_CODE');
        }
    }
}

