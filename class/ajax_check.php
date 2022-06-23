<?

// пример обработчика ajax запроса на проверку кода и авторизации / авторегистрации

$arResponse = [];

$formattdPhone = '';

do {


    if (!\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
        $arResponse['error'] = 'Не установлен модуль авторизации по номеру телефона';
        break;
    }


    $oManagerAuthUserPhone = \BXmaker\AuthUserPhone\Manager::getInstance();

    $oFormat = new \BXmaker\AuthUserPhone\Format();

    $req = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

    $phone = $oManagerAuthUserPhone->getPreparedPhone((string)$req->getPost('phone'));
    $code = trim((string)$req->getPost('code'));

    if (!$oManagerAuthUserPhone->isValidPhone($phone)) {
        $arResponse['error'] = 'Номер мобильного телефона указан не верно';
        break;
    }

    if (strlen($code) <= 0) {
        $arResponse['error'] = 'Не указан код из смс';
        break;
    }


    $formattdPhone = $oFormat->getFormatedPhone($phone, true, true, true, true);

    try {

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


        $arResponse['msg'] = 'Авторизация прошла успешно';


    } catch (\BXmaker\AuthUserPhone\Exception\BaseException $ex) {
        $arResponse['error'] = $ex->getMessage();
        $arResponse['more'] = $ex->getCustomCode();
    }
} while (false);

$arResponse['formattedPhone'] = $formattdPhone;


echo \Bitrix\Main\Web\Json::encode($arResponse);


