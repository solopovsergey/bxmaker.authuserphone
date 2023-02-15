<?php


/**
 * Пример авторизации по карте лояльности и паролю
 * для этого для пользователя добавляется поле типа строка, множественное с кодом
 * обработчик для компонента Enter
 * в настройках модуля включить возможность входа по логину
 */

// содержимое файла /bitrix/php_interface/user_lang/ru/lang.php
$MESS["/local/js/bxmaker/authuserphone/enter/lang/ru/config.php"]["BXMAKER_AUTHUSERPHONE_ENTER_AUTH_BY_PASSWORD_FORM_LOGIN"] = "Карта лояльности ";


// обработчик событий для файла init.php
if (\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone')) {


    $eventManager = \Bitrix\Main\EventManager::getInstance();
    $eventManager->addEventHandler(
        "bxmaker.authuserphone",
        "BXmakerAuthUserPhoneEnterComponentAjax",
        "bxmaker_authuserphone_loyality_card"
    );


    function bxmaker_authuserphone_loyality_card(\Bitrix\Main\Event $event)
    {
        /**
         * @var $jsonResponse \BXmaker\AuthUserPhone\Ajax\JsonResponse
         * @var $component \BXmakerAuthUserPhoneCallComponent
         */
        $fields = $event->getParameter('fields');
        $jsonResponse = $fields['jsonResponse'];
        $component = $fields['component'];


        $actionType = $component->request()->getPost('actionType');
        if ($actionType !== 'AUTH') {
            return;
        }

        $phoneOrCard = (string)$component->request()->getPost('ple');
        if (empty($phoneOrCard)) {
            return;
        }

        // если введен номер телефона пропускаем
        if ($component->manager()->isValidPhone($phoneOrCard)) {
            return;
        }


        /**
         * @var  \CUser $oldUser
         */
        $oldUser = $component->manager()->oldUser();
        $dbrUser = $oldUser::getList('', '', [
            'ACTIVE' => 'Y',
            'UF_LOYALTY_CARD' => $phoneOrCard
        ], [
            'SELECT' => [
                'UF_LOYALTY_CARD'
            ],
            'FIELDS' => [
                'ID', 'NAME', 'PASSWORD'
            ],
            'NAV_PARAMS' => [
                'nPageSize' => 1
            ]
        ]);
        $arUser = $dbrUser->Fetch();

        if (!$arUser) {
            return;
        }

        $password = $component->request()->getPost('password');
        if (!$component->manager()->isValidUserPassword($arUser, $password)) {
//            throw new \BXmaker\AuthUserPhone\Exception\BaseException('Такой пользователь не найден ', 'USER_NOT_FOUND');
            throw new \BXmaker\AuthUserPhone\Exception\BaseException('Не верно указана крата лояльности или пароль', 'ERROR_PLE_OR_PASSWORD');
        }

        $authResult = $component->manager()->authorize($arUser['ID']);
        if (!$authResult->isSuccess()) {
            $authResult->throwException();
        }

        $arResponse = $component->extendResponseAfterAuth($arUser['ID'], [
            'msg' => 'Вы успешно авторизовались',
            'type' => ($component->manager()->isSetUserRegisterFlag() ? 'REG' : 'AUTH')
        ]);

        $jsonResponse->setResponse($arResponse);
        $jsonResponse->output();
    }

}



