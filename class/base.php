<?

// базовые примеры использования

// Подклчюение модуля

if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {
    $oManager = \BXmaker\AuthUserPhone\Manager::getInstance();

    //операции ...
    $phone = $oManager->getPreparedPhone('8 999-111-22-33');
    echo $phone; // 79991112233
}


$oManager = \BXmaker\AuthUserPhone\Manager::getInstance();

// Result
// всегда либо успешно либо содержит ошибку

$result = new \BXmaker\AuthUserPhone\Result();

// Передаем основной результат
$result->setResult(10);
echo $result->getResult(); // 10

// Передаем дополнитльные данные
$result->setMore('MSG', 'Смс отправлено');
echo $result->getMore('MSG'); //Смс отправлено


// проверяем успешный ли результат
if ($result->isSuccess()) {
    echo (string)$result->getResult();
}


// Работа с ошибками
// Если в процессе операции возникла ошибка, то можем ее получить и обработать.
// Возвращаеться всегда будет объект результата содержащий в себе ошибку,
//  объект класса \BXmaker\Authuserphone\Error

// Работа с ошибкой
$result = new \BXmaker\AuthUserPhone\Result();
$result->createError(
    'Телефон не валидный',
    'ERROR_PHONE_INVALID',
    [
        'captcha' => \BXmaker\AuthUserPhone\Manager::getInstance()->captcha()->getForJs()
    ]
);


// когда необходимо передать больше данных весте с ошибкой
$result->createError(
    'Введите код с картинки',
    'ERROR_NEED_CAPTCHA',
    [
        'captcha' => \BXmaker\AuthUserPhone\Manager::getInstance()->captcha()->getForJs()
    ]
);


// Чтобы получить данные по ошибке
echo $result->getFirstError()->getCode(); //  ERROR_NEED_CAPTCHA
echo $result->getFirstError()->getMessage(); //  Введите код с картинки
var_export($result->getFirstError()->getMore()); //  ['captcha' => [...]]


// Если нужно создать ошибку из исключения, то делаем следующее
$ex = new \Exception('Error');
$result->createErrorFromException($ex);
echo $result->getFirstError()->getMessage(); //  Error


// Когда нужно выбросить исключение при наличии ошибки
if (!$result->isSuccess()) {
    $result->throwException();
    // throw new \BXmaker\AuthUserPhone\Exception\BaseException()
}


//  Подготовка номера телефона
//Для использвоания номера телефона, его необходимо привести к виду пригодному для повсеместного использования

$phone = $oManager->getPreparedPhone('+7 (999 111 22-33');
echo $phone; // 79991112233

// Проверка валидности номера телефона
if ($oManager->isValidPhone($phone)) {
    echo 'Номер телефона введен верно';
}

// Проверка достижения лимита
// Если лимит превышен выбросит исключение о необходимости капчи например, если данные о капчи пришли вместе с запросом и валидные, пропустит дальше

// Подготовка
// по ip адресу
$oManager->limitIP()->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE);

//для лимитов с привязкой к номеру, передаем номер
$oManager->limit()->setType(\BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_SMS_CODE)->setPhone($phone);

// проверяем лимты
// по ip адресу
$oManager->limitIP()->checkCanDoRequest();

//для лимитов с привязкой к номеру, передаем номер
$oManager->limit()->checkCanDoRequest();

// Фиксируем попытку запроса
// по ip адресу
$oManager->limitIP()->setRequest();

//для лимитов с привязкой к номеру
$oManager->limit()->setRequest();


// Фиксируем попытку проверки
// по ip адресу
$oManager->limitIP()->setCheck();

//для лимитов с привязкой к номеру
$oManager->limit()->setCheck();

//  получение таймаута между запросами начала подтверждений, например смс код
$time = $oManager->getSmsCodeTimeout($phone);
$time = $oManager->getUserCallTimeout($phone);
$time = $oManager->getBotCallTimeout($phone);

//если время между запросами еще не вышло, то выбросит соответствующее исключение
$oManager->checkSmsCodeTimeout($phone);
$oManager->checkUserCallTimeout($phone);
$oManager->checkBotCallTimeout($phone);


// Поиск по номеру телефона и паролю
$phone = '79991112233';
$password = 'JIO^fne64V+3';

$userIdResult = $oManager->findUserIdByPhonePassword($phone, $password);
if ($userIdResult->isSuccess()) {
    $userId = (int)$userIdResult->getResult();
}


// авторизация
// Авторизацет пользователя,  необходимо вызывать после всех проверок

$userId = 10;

$resultAuth = $oManager->authorize($userId);
if (!$resultAuth->isSuccess()) {
    //  можно например выбросить исключение, чтобы не выполнять код далее
    $resultAuth->throwException();
}

// Авторегистрация

//  если пользвоателя с таким номером телефона нет, попробуйем его зарегистрироваться
$arUserFields = [];
$registerResult = $oManager->register($phone, $arUserFields);
if (!$registerResult->isSuccess()) {
    $registerResult->throwException();
}

$userId = (int)$registerResult->getResult();

// Получение настроек
$oManager->option()->isEnabledAutoRegister();


// Смена сайта
$oManager->setSiteId('s2');

// автоопределение
$oManager->setSiteId();












