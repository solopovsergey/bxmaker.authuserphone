<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}


//файл для модуля bxmaker.api
// отправка запроса на звонок от пользователя, чтобы получить номер телефона на который нужно позвонить ему


if (!\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
    $this->setError('Не установлен модуль авторизации по номеру телефона', '1');
    return false;
}


$oManagerAuthUserPhone = \BXmaker\AuthUserPhone\Manager::getInstance();

$oFormat = new \BXmaker\AuthUserPhone\Format();

$phone = $oManagerAuthUserPhone->getPreparedPhone((string)$this->get('phone'));

if (!$oManagerAuthUserPhone->isValidPhone($phone)) {
    $this->setError('Номер мобильного телефона указан не верно', 'ERROR_PHONE_INVALID');
    return false;
}

$formattdPhone = $oFormat->getFormatedPhone($phone, true, true, true, true);


try {
    $captchaId = trim($this->get('captchaId'));
    $captchaCode = trim($this->get('captchaCode'));
    if ($captchaId && $captchaCode) {
        $post = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList();
        $post->set('captchaId', $captchaId);
        $post->set('captchaCode', $captchaCode);
    }

    $oManagerAuthUserPhone
        ->limitIP()
        ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_USER_CALL)
        ->checkCanDoRequest();

    $oManagerAuthUserPhone
        ->limit()
        ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_USER_CALL)
        ->setPhone($phone)
        ->checkCanDoRequest();

    $oManagerAuthUserPhone->checkUserCallTimeout($phone);

    $oManagerAuthUserPhone->limitIp()->setRequest();
    $oManagerAuthUserPhone->limit()->setRequest();

    $startUserCallResult = $oManagerAuthUserPhone->service()->startUserCall($phone);
    if (!$startUserCallResult->isSuccess()) {
        $startUserCallResult->throwException();
    }

    $this->setResult(
        [
            'FORMATTED_PHONE' => $formattdPhone,
            'TIMEOUT' => $startUserCallResult->getMore('TIMEOUT'),
            'CALL_TO' => $startUserCallResult->getMore('CALL_TO'),
            'MSG' => $startUserCallResult->getMore('MSG'),
        ]
    );
} catch (\BXmaker\AuthUserPhone\Exception\BaseException $ex) {
    $this->setError(
        $ex->getMessage(),
        $ex->getCustomCode(),
        array_merge(
            $ex->getCustomData(),
            [
                'FORMATTED_PHONE' => $formattdPhone
            ]
        )
    );
}



