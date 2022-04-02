<?php

// Пример копирвоания ФИО из заказа в профиль пользователя

// Код поместите в /bitrix/php_interface/init.php


$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    "sale",
    "OnSaleOrderSaved",
    ['CustomSaleEventHandler', "sale_OnSaleOrderSaved"]
);

class CustomSaleEventHandler
{
    public static function sale_OnSaleOrderSaved(\Bitrix\Main\Event $event)
    {
        global $USER;

        $arOptions = [
            [
                'PERSON_ID' => 1,
                'PROP_FIO' => 'FIO',
                'PROP_EMAIL' => 'EMAIL'
            ],
            // здесь можно добавить варианты для других типов плательщика также
        ];


        /** @var \Bitrix\Sale\Order $order */
        $order = $event->getParameter("ENTITY");
        $isNew = $event->getParameter("IS_NEW");

        if (!$isNew) {
            return true;
        }

        $userId = $order->getUserId();

        // првоеряем заполненость полей профиля пользователя
        $arUser = $USER->GetList('', '', [
            'ID' => $userId
        ])->fetch();
        if (!$arUser) {
            return false;
        }

        $bNeedFio = false;
        if (empty($arUser['NAME']) || empty($arUser['LAST_NAME']) || empty($arUser['SECOND_NAME'])) {
            $bNeedFio = true;
        }

        $bNeedEmail = false;
        if (empty($arUser['EMAIL'])) {
            $bNeedEmail = true;
        }

        //если все заполнено - ничего не делаем  ------------
        if (!$bNeedFio && !$bNeedEmail) {
            return false;
        }

        $arUpdateFields = [];


        foreach ($arOptions as $option) {
            if ($order->getPersonTypeId() != $option['PERSON_ID'] || empty($option)) {
                continue;
            }

            /**
             * @var \Bitrix\Sale\PropertyValue
             */

            // получим фио ----------
            $prop = $order->getPropertyCollection()->getItemByOrderPropertyCode($option['PROP_FIO']);
            if ($prop && $bNeedFio) {
                $name = $prop->getValue();
                $name = preg_replace('/\s+/', ' ', $name);
                $name = trim($name);
                $arName = explode(' ', $name);

                if ($bNeedFio && !empty($arName)) {
                    $arUpdateFields['LAST_NAME'] = '';
                    $arUpdateFields['NAME'] = '';
                    $arUpdateFields['SECOND_NAME'] = '';

                    if (count($arName) >= 3) {
                        $arUpdateFields['LAST_NAME'] = $arName[0];
                        $arUpdateFields['NAME'] = $arName[1];
                        $arUpdateFields['SECOND_NAME'] = $arName[2];
                    } elseif (count($arName) == 2) {
                        $arUpdateFields['LAST_NAME'] = $arName[0];
                        $arUpdateFields['NAME'] = $arName[1];
                    } elseif (count($arName) == 1) {
                        $arUpdateFields['NAME'] = $arName[0];
                    }
                }
            }


            // найдем email ---
            $prop = $order->getPropertyCollection()->getItemByOrderPropertyCode($option['PROP_EMAIL']);
            if ($prop && $bNeedEmail) {
                $email = $prop->getValue();

                if ($bNeedEmail && check_email($email)) {
                    $arUpdateFields['EMAIL'] = $email;
                }
            }

        }

        // заменим поля если найшли чем
        if (!empty($arUpdateFields)) {
            $USER->Update($userId, $arUpdateFields);
        }

        return true;
    }

}
