<?

namespace BXmaker\AuthUserPhone\Service;

use Bitrix\Main\Localization\Loc;
use BXmaker\AuthUserPhone\Result;

Loc::loadLanguageFile(__FILE__);

/**
 * Пример своего сервиса для подтверждения номера телефона
 * @package BXmaker\AuthUserPhone\Service
 */
class Customservice extends \BXmaker\AuthUserPhone\Service\Base
{

    /**
     * @inherit
     */
    public static function getMessage($name, $arReplace = [])
    {
        return Loc::getMessage('CUSTOM_SERVICE.' . $name, $arReplace);
    }

    /**
     * Название сервиса
     *
     * @return string
     */
    public static function getName()
    {
        return 'Кастомный сервис';
    }


    /**
     * Описание сервиса
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'Описание, подсказка как заполнить поля для подключения и тп ';
    }

    /**
     * Парамтеры сервиса для работы с ним, например логин и пароль.
     * Получить занчение парамтера можно через $this->>getParams('LOGIN')
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'LOGIN' => [
                'NAME' => 'логин',
                'HELP' => '',
                'TYPE' => 'STRING',
            ],
            'PASSWORD' => [
                'NAME' => 'пароль',
                'HELP' => '',
                'TYPE' => 'PASSWORD',
            ]
        ];
    }

    /**
     * Првоерка доступности подтверждения через звонок от бота
     *
     * @return bool
     */
    public static function isAvailableBotCall()
    {
        return true;
    }

    /**
     * Проверка доступности подтверждения верез смс код
     *
     * @return bool
     */
    public static function isAvailableSms()
    {
        return true;
    }

    /**
     * Подтверждение через звонок от пользователя доступен или нет
     *
     * @return bool
     */
    public static function isAvailableUserCall()
    {
        return true;
    }

    /**
     * Класс поддерживает проверку через голосовой код
     * @return true
     */
    public static function isAvailableBotSpeech()
    {
        return true;
    }

    /**
     * Класс поддерживает проверку через SimPush
     * @return true
     */
    public static function isAvailableSimPush()
    {
        return true;
    }


    /**
     * Отправка смс
     *
     * @param $phone
     * @param $text
     *
     * @return Result
     */
    public function sendSms($phone, $text)
    {
        $arFields = [
           'phone' => $phone,
            'text' => $text,
        ];

        $result = $this->request('/sms/send', $arFields);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            $result->setResult($data["message"]);
            $result->setMore('EXT_ID', $data["message"]["id"] ?? null); // уникальный идентификатор сообщения в сервисе
        }

        return $result;
    }

    /**
     * Запрос у сервиса номера телеофна, для подтверждения звонком от пользователя
     *
     * @param $phone
     *
     * @return Result
     */
    public function startUserCall($phone)
    {
        $result = $this->request('/call', [
            'phone' => $phone,
            'webhookUrl' => $this->getCallbackUrl(), // url на которй будет отправлено уводмление от сервиса, после полступления звонка пот пользвоателя
        ]);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            // униклаьны идентификатор
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));

            // номер телеофна на который нужно будет позвонить пользвоателю, выводится в публичной части ему
            $result->setMore('CALL_TO', ($data["result"]["mobile"] ?? null));
        }

        return $result;
    }


    /**
     * Звонок от бота, код в номере телефона
     *
     * @param $phone
     * @param $code
     *
     * @return Result
     */
    public function startBotCall($phone, $code)
    {
        $result = $this->request('/botcall', [
            'phone' => $phone,
            'code' => $code
        ]);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            // если сервис отправляет свои коды, переопределим конечное значнеие
            if (!empty($data["result"]["code"])) {
                $code = (string)$data["result"]["code"];
            }

            // униклаьны идентификатор
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
            // код, который должен указать пользователь, содержащийся в последних цыфрах входящего номера
            $result->setMore('CODE', $code);
        }
        return $result;
    }

    /**
     * Звонок от робота, который произнесен код
     * @param $phone
     * @param $code
     *
     * @return \BXmaker\AuthUserPhone\Result
     */
    public function startBotSpeech($phone, $code)
    {

        $text =  'Ваш одноразовый код';
        $text .= '.-.'.implode('.-.', str_split($code));

        // получится например - Ваш одноразовый код .-.1.-.5.-.3.-.8

        $arFields = [
            'phone' => $phone,
            'text' => $text
        ];

        $result = $this->request('/voice', $arFields);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            // униклаьны идентификатор
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
            // код, который должен указать пользователь, произнесенный роботом
            $result->setMore('CODE', $code);
        }
        return $result;
    }


    /**
     * Старт отправки SIM-push
     */
    public function startSimPush($phone)
    {
        $result = $this->request(self::REQUEST_TYPE_SIM_PUSH, [
            "route" => 'pushok',
            "phone" => $phone,
        ]);
        if ($result->isSuccess()) {
            $response = $result->getResult();

            // униклаьны идентификатор
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
        }
        return $result;
    }

    /**
     * Запрос обобщеный
     *
     * @param $url
     * @param $arFields
     *
     * @return Result
     */
    public function request($url, $arFields)
    {
        $result = new Result();

        $oHttp = $this->getHttpClient(self::CONTENT_TYPE_JSON);
        $oHttp->setAuthorization($this->getParam('LOGIN'), $this->getParam('PASSWORD'));


        $oHttp->post('https://api.service.ru/json' . $url, $this->toJson($arFields));

        $data = $this->fromJson($oHttp->getResult());

        //если не удалось распознать json ответ
        if (!$data) {
            return $result->createError(
                'Неизвестный ответ сервиса',
                'SERVICE_ERROR_UNKNOW'
            );
        }

        // если произошла ошибка
        if (!$data['success']) {
            return $result->createError(
                sprintf('[%s] %s', $data['error']['code'], $data['error']['descr']),
                'SERVICE_ERROR_CUSTOM'
            );
        }

        // иначе ответ передаем на дальнейшую обработку
        $result->setResult($data);

        return $result;
    }


    /**
     * Обработчик колбэка на звонок от пользвоателя, подтерждение от simPush
     * @return false|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function callback()
    {
        $req = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
        $sid = (string)$req->get('sid');
        $md5 = (string)$req->get('md5');
        $id = (string)$req->getJsonList()->get('id');

        if (!$sid || !$md5 || !$id) {
            echo 'не все параметры пришли';
            return false;
        }


        if ($md5 !== $this->getMDKey()) {
            echo 'не верная подпись';
            return false;
        }

        $oHistory = new  HistoryTable();


        // Авторизация пройдена успешно. Мы получили звонок с номера, который вы нам передавали.
        $dbrHistory = $oHistory->getList([
            'order' => ['ID' => 'DESC'],
            'filter' => [
                '=SITE_ID' => $this->getSiteId(),
                '=CONFIRM_TYPE' => \BXmaker\AuthUserPhone\Manager::CONFIRM_TYPE_USER_CALL,
                '=EXT_ID' => $id,
            ],
            'limit' => 1
        ]);
        if ($arHistory = $dbrHistory->fetch()) {
            // отмечаем что звонок поступил
            $this->setUserCallConfirmed($arHistory['ID']);

            // если simPush, отмечаем что запрос принят на устройстве пользователя
            $this->setSimPushConfirmed($arHistory['ID']);
        }

        echo 'OK';

    }


    /**
     * Адерс обработчика оповещений, вернет
     * https://site.ru/bitrix/tools/bxmaker.authuserphone/callback.php?sid={$this->getId()}&md5={$this->getMDKey()};
     * $this->getId() возвращает идентфииктаор настроек сервиса
     *
     * @return int|null|string
     */
    public function getCallbackUrl()
    {
        return parent::getCallbackUrl() . '&md5=' . $this->getMDKey();
    }

    public function getMDKey()
    {
        return md5(substr($this->getParam('LOGIN').'|'.$this->getParam('PASSWORD'), 0, 10));
    }

    /**
     * Проверка наличия у сервиса колбэка (оповещений с их сервиса), если есть, то  будет использоваться обработчик,
     * если нет колбэка, то при проверке статуса будет происходить тут же запрос к сервису
     *
     * @return bool
     */
    public function hasCallback()
    {
        return false;
    }


}
