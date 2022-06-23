<?php

/**
 * Пример вызова события при разработке, вместо нажатия по кнопкам и ввода данных
 * состав парамтеров должен соответствовать описанному в комментарии к константе
 * события в исходниках иили документации
 */

//=========================================================================
// отправка временного кода в смс
$sendEventResult = \BXmaker\AuthUserPhone\Manager::getInstance()->sendEvent(
    \BXmaker\AuthUserPhone\Manager::EVENT_ON_SEND_SMS_CODE,
    [
        'PHONE' => '79991112233',
        'CODE' => time()
    ]
);

if ($sendEventResult->isSuccess()) {
    echo '<pre>';
    print_r($sendEventResult->getMore());
    echo '</pre>';

}


//=========================================================================
//  добален польвзаотель
$oManager = \BXmaker\AuthUserPhone\Manager::getInstance();
$resultEvent = $oManager->sendEvent(
    \BXmaker\AuthUserPhone\Manager::EVENT_ON_USER_ADD,
    [
        'PHONE' => '79991112233',
        'PASSWORD' => 'pass',
        'ID' => '1',
        'USER_ID' => '1'
    ]
);
if (!$resultEvent->isSuccess()) {
    $error = true;
} else {
    $error = false;
}
