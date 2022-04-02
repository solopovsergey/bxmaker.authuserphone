<?php

/**
 * Пример вызова соыбтия при разработке, вместо нажатия по кнопкам и ввода данных
 * состав парамтеров долежн соответствовать описанному в комментарии к константе
 * события в исодниках иили докумнетации
 */

$bTest = false;
$sendEventResult = \Bxmaker\AuthUserPhone\Manager::getInstance()->sendEvent(
    \BXmaker\AuthUserPhone\Manager::EVENT_ON_SEND_SMS_CODE,
    [
        'PHONE' => '79991112233',
        'CODE' => time()
    ]
);

if ($sendEventResult->isSuccess()) {
    if (!is_null($sendEventResult->getMore('TEST'))) {
        $bTest = (bool) $sendEventResult->getMore('TEST');
    }
}

