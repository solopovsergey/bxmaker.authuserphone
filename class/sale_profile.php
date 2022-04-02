<?

// Создание профиля покупателя при  регистрации по номеру телефона,
// чтобы при переходе на страницу оформления заказа
// поле телеофн у пользователя уже было заполнено из сгенерированного профиля


//  Имитация события, для отладки
//--------------------------------------------------

\Bitrix\Main\Loader::includeModule('bxmaker.authuserphone');


// Пример генерации события, для проверки обработчика ниже

$oManager = \Bxmaker\AuthUserPhone\Manager::getInstance();
$sendEventResult = $oManager->sendEvent(
    \Bxmaker\AuthUserPhone\Manager::EVENT_ON_USER_ADD,
    [
        'PHONE' => '79991112233', // телефона
        'PASSWORD' => 'testPassword'.time(), //пароль
        'ID' => '10', //  идентификатор пользователя
        'USER_ID' => '10', //идентификатор пользвоателя
    ]
);



// Обработчик события,  который можно поместить в /bitrix/php_interface/init.php
//---------------------------------------------------------------------

// подписаываемя на событие модуля
$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler(
    "bxmaker.authuserphone",
    "onUserAdd",
    "bxmaker_authuserphone_onUserAdd"
);



// обработчики события
function bxmaker_authuserphone_onUserAdd(\Bitrix\Main\Event $event)
{
    //идентификтаор типа плательщика
    $personalTypeId = 1;
    //идентификтаор свойства для хранения номера телефона
    $phonePropId = 3;

    $fields = $event->getParameter('fields');


    //массив свойств создаваемого профиля
    $arProfileProps = [
        [
            "ORDER_PROPS_ID" => $phonePropId, //идентификатор свойства заказа с номером телефона для конкретного типа плательщика
            "NAME" => "Телефон",
            "VALUE" => $fields['PHONE']
        ]
    ];

    if (\Bitrix\Main\Loader::includeModule('sale')) {

        //создаём новый профиль --------
        $arProfileFields = [
            "NAME" => "Профиль покупателя (" . $fields['PHONE'] . ')',
            "USER_ID" => intval($fields['USER_ID']),
            "PERSON_TYPE_ID" => $personalTypeId
        ];

        $profileId = CSaleOrderUserProps::Add($arProfileFields);

        //если профиль создан
        if ($profileId) {

            //добавляем значения свойств к созданному ранее профилю
            foreach ($arProfileProps as $arProp) {

                $resultProp = CSaleOrderUserPropsValue::Add(array_merge($arProp, [
                    "USER_PROPS_ID" => $profileId,
                ]));
            }
        }
    }

    $result = new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, []);
    return $result;
}
