<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

//файл для модуля bxmaker.api
// запрос проверки подтверждения,  проверка  кода в смс


/**
 * @var \BXmaker\Api\Handler $this
 */

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


    $code = trim((string)$this->get('code'));

    if (strlen($code) <= 0) {
        $this->setError(
            'Не указан код из смс',
            'ERROR_CODE'
        );
        return false;
    }


    $oManagerAuthUserPhone
        ->limitIP()
        ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE)
        ->checkCanDoCheck();

    $oManagerAuthUserPhone
        ->limit()
        ->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE)
        ->setPhone($phone)
        ->checkCanDoCheck();

    $oManagerAuthUserPhone->limitIp()->setCheck();
    $oManagerAuthUserPhone->limit()->setCheck();

    $checkSmsCodeResult = $oManagerAuthUserPhone->service()->checkSmsCode($phone, $code);
    if (!$checkSmsCodeResult->isSuccess()) {
        $checkSmsCodeResult->throwException();
    }


    $userId = null;

    $findUserResult = $oManagerAuthUserPhone->findUserIdByPhone($phone, true);
    if ($findUserResult->isSuccess()) {
        $userId = (int)$findUserResult->getResult();
    } else {
        $findInactiveResult = $oManagerAuthUserPhone->findUserIdByPhone($phone, false);
        if ($findInactiveResult->isSuccess()) {
            throw new \BXmaker\AuthUserPhone\Exception\BaseException('Пользователь заблокирован', 'ERROR_USER_ACTIVE');
        }
    }

    //регистрируем если надо
    if (is_null($userId) && $oManagerAuthUserPhone->param()->isEnabledAutoRegister()) {
        $registerResult = $oManagerAuthUserPhone->register($phone);
        if (!$registerResult->isSuccess()) {
            $registerResult->throwException();
        }

        $userId = (int)$registerResult->getResult();
    }

    // не удалось определить
    if (is_null($userId)) {
        throw new \BXmaker\AuthUserPhone\Exception\BaseException('Пользователь не найден', 'ERROR_USER_ID');
    }

    $authResult = $oManagerAuthUserPhone->authorize($userId);
    if (!$authResult->isSuccess()) {
        $authResult->throwException();
    }


    $this->setResult(array(
        'msg' => 'Авторизация прошла успешно',
    ));


} catch (\BXmaker\AuthUserPhone\Exception\BaseException $ex) {
    $this->setError(
        $ex->getMessage(),
        $ex->getCustomCode(),
        array_merge(
            $ex->getCustomData(),
            [
                'FORMATTED_PHONE' => $formattdPhone,
            ]
        )
    );
}


    