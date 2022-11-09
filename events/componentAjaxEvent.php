<?php


// пример реалиации автовхода пользователя при указании номера телефона  7 999 999 99 99
//  можно сделать для любого комопнента, нужно только подпистаься на его событие
// для каждого комопнента оно свое


$eventManager = \Bitrix\Main\EventManager::getInstance();

$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "BXmakerAuthUserPhoneSimpleComponentAjax",
    "bxmaker_authuserphone_autoauth"
);


$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "BXmakerAuthUserPhoneSimpleComponentAjaxAnswer",
    "bxmaker_authuserphone_autoauthAnswer"
);


function bxmaker_authuserphone_autoauth(\Bitrix\Main\Event $event)
{
    //можно выбрасывать исключения

    /**
     * @var $jsonResponse \BXmaker\AuthUserPhone\Ajax\JsonResponse
     * @var $component \BXmakerAuthUserPhoneCallComponent
     */
    $fields = $event->getParameter('fields');
    $jsonResponse = $fields['jsonResponse'];
    $component = $fields['component'];

    $phone = $component->request()->getPost('phone');

    $phone = $component->manager()->getPreparedPhone($phone);


    if ($component->manager()->isValidPhone($phone) && $phone == '79999999999') {


        $findUserResult = $component->manager()->findUserIdByPhone($phone, false);
        if ($findUserResult->isSuccess()) {
            $userId = (int)$findUserResult->getResult();
        } else {
            $registerResult = $component->manager()->register($phone);
            if (!$registerResult->isSuccess()) {
                $registerResult->throwException();
            }

            $userId = (int)$registerResult->getResult();
        }


        $authResult = $component->manager()->authorize($userId);
        if (!$authResult->isSuccess()) {
            $authResult->throwException();
        }

        $component->arParams['IS_ENABLED_RELOAD_AFTER_AUTH'] = 'Y';

        $arResponse = $component->extendResponseAfterAuth($userId, [
            'msg' => 'Вы успешно авторизовались',
            'type' => ($component->manager()->isSetUserRegisterFlag() ? 'REG' : 'AUTH')
        ]);

        $jsonResponse->setResponse($arResponse);
        $jsonResponse->output();

    }

}

function bxmaker_authuserphone_autoauthAnswer(\Bitrix\Main\Event $event)
{
    // НЕЛЬЗЯ выбрасывать исключения

    /**
     * @var $jsonResponse \BXmaker\AuthUserPhone\Ajax\JsonResponse
     * @var $component \BXmakerAuthUserPhoneCallComponent
     */
    $fields = $event->getParameter('fields');
    $jsonResponse = $fields['jsonResponse'];
    $component = $fields['component'];

    // добавим данные, можно сделать что-то посложнее
    $jsonResponse->setResponseField('date', date('d.n.Y'));


}
