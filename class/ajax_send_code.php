<?php

// Пример файла обработчика ajax запроса на отправку временного кода в смс

$arResponse = [];

$formattdPhone = '';

do {

    if (!\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
        $arResponse['error'] = 'Не установлен модуль авторизации по номеру телефона';
        break;
    }

    $oManagerAuthUserPhone = \BXmaker\AuthUserPhone\Manager::getInstance();

    $req = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

    $phone = $oManagerAuthUserPhone->getPreparedPhone((string)$req->getPost('phone'));

    if (!$oManagerAuthUserPhone->isValidPhone($phone)) {
        $arResponse['error'] = 'Номер мобильного телефона указан не верно';
        break;
    }

    $formattdPhone = $oManagerAuthUserPhone->format()->international($phone);

    try {

        $oManagerAuthUserPhone
            ->limitIP()
            ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE)
            ->checkCanDoRequest();

        $oManagerAuthUserPhone
            ->limit()
            ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE)
            ->setPhone($phone)
            ->checkCanDoRequest();

        $oManagerAuthUserPhone->checkSmsCodeTimeout($phone);

        $oManagerAuthUserPhone->limitIp()->setRequest();
        $oManagerAuthUserPhone->limit()->setRequest();

        $startSmsCodeResult = $oManagerAuthUserPhone->service()->startSmsCode($phone);
        if (!$startSmsCodeResult->isSuccess()) {
            $startSmsCodeResult->throwException();
        }

        $arResponse['timeout'] = $startSmsCodeResult->getMore('TIMEOUT');
        $arResponse['length'] = $startSmsCodeResult->getMore('LENGTH');
        $arResponse['msg'] = $startSmsCodeResult->getMore('MSG');


    } catch (\BXmaker\AuthUserPhone\Exception\BaseException $ex) {

        $arResponse['error'] = $ex->getMessage();
        $arResponse['more'] = $ex->getCustomCode();
    }

} while (false);

$arResponse['formattedPhone'] = $formattdPhone;


echo \Bitrix\Main\Web\Json::encode($arResponse);








