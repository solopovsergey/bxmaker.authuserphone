<?php



$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "\Bxmaker\AuthUserPhone\Manager\Limit::OnBeforeAdd",
    "bxmaker_authuserphone_manager_limit_onBeforeAdd"
);


/**
 * Обработчик события вызываемые перед добавлением записи в таблицу лимитов
 * с привязкой к номеру телефона
 * @param \Bitrix\Main\Entity\Event $event
 * @return \Bitrix\Main\Entity\EventResult
 */
function bxmaker_authuserphone_manager_limit_onBeforeAdd(\Bitrix\Main\Entity\Event $event)
{
    $result = new \Bitrix\Main\Entity\EventResult;

    $fields = $event->getParameter("fields");

    // при добавлении записи в базу, сразу будем считать что человек
    // попытался 5 раз запросить код в смс, но только для определенного
    // нмоера телеофна

    if($fields['PHONE'] === '79991112233')
    {
        $result->modifyFields(array('ATTEMPT_REQUEST_SMS_CODE' => 5));
    }

    return $result;
}
