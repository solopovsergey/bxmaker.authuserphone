<?

namespace BXmaker\AuthUserPhone\Service;

use Bitrix\Main\Localization\Loc;
use BXmaker\AuthUserPhone\Result;

Loc::loadLanguageFile(__FILE__);

/**
 * ������ ������ ������� ��� ������������� ������ ��������
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
     * �������� �������
     *
     * @return string
     */
    public static function getName()
    {
        return '��������� ������';
    }


    /**
     * �������� �������
     *
     * @return string
     */
    public static function getDescription()
    {
        return '��������, ��������� ��� ��������� ���� ��� ����������� � �� ';
    }

    /**
     * ��������� ������� ��� ������ � ���, �������� ����� � ������.
     * �������� �������� ��������� ����� ����� $this->>getParams('LOGIN')
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'LOGIN' => [
                'NAME' => '�����',
                'HELP' => '',
                'TYPE' => 'STRING',
            ],
            'PASSWORD' => [
                'NAME' => '������',
                'HELP' => '',
                'TYPE' => 'PASSWORD',
            ]
        ];
    }

    /**
     * �������� ����������� ������������� ����� ������ �� ����
     *
     * @return bool
     */
    public static function isAvailableBotCall()
    {
        return true;
    }

    /**
     * �������� ����������� ������������� ����� ��� ���
     *
     * @return bool
     */
    public static function isAvailableSms()
    {
        return true;
    }

    /**
     * ������������� ����� ������ �� ������������ �������� ��� ���
     *
     * @return bool
     */
    public static function isAvailableUserCall()
    {
        return true;
    }

    /**
     * ����� ������������ �������� ����� ��������� ���
     * @return true
     */
    public static function isAvailableBotSpeech()
    {
        return true;
    }

    /**
     * ����� ������������ �������� ����� SimPush
     * @return true
     */
    public static function isAvailableSimPush()
    {
        return true;
    }


    /**
     * �������� ���
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
            $result->setMore('EXT_ID', $data["message"]["id"] ?? null); // ���������� ������������� ��������� � �������
        }

        return $result;
    }

    /**
     * ������ � ������� ������ ��������, ��� ������������� ������� �� ������������
     *
     * @param $phone
     *
     * @return Result
     */
    public function startUserCall($phone)
    {
        $result = $this->request('/call', [
            'phone' => $phone,
            'webhookUrl' => $this->getCallbackUrl(), // url �� ������ ����� ���������� ���������� �� �������, ����� ������������ ������ ��� ������������
        ]);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            // ��������� �������������
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));

            // ����� �������� �� ������� ����� ����� ��������� ������������, ��������� � ��������� ����� ���
            $result->setMore('CALL_TO', ($data["result"]["mobile"] ?? null));
        }

        return $result;
    }


    /**
     * ������ �� ����, ��� � ������ ��������
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

            // ���� ������ ���������� ���� ����, ������������� �������� ��������
            if (!empty($data["result"]["code"])) {
                $code = (string)$data["result"]["code"];
            }

            // ��������� �������������
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
            // ���, ������� ������ ������� ������������, ������������ � ��������� ������ ��������� ������
            $result->setMore('CODE', $code);
        }
        return $result;
    }

    /**
     * ������ �� ������, ������� ���������� ���
     * @param $phone
     * @param $code
     *
     * @return \BXmaker\AuthUserPhone\Result
     */
    public function startBotSpeech($phone, $code)
    {

        $text =  '��� ����������� ���';
        $text .= '.-.'.implode('.-.', str_split($code));

        // ��������� �������� - ��� ����������� ��� .-.1.-.5.-.3.-.8

        $arFields = [
            'phone' => $phone,
            'text' => $text
        ];

        $result = $this->request('/voice', $arFields);

        if ($result->isSuccess()) {
            $data = $result->getResult();

            // ��������� �������������
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
            // ���, ������� ������ ������� ������������, ������������� �������
            $result->setMore('CODE', $code);
        }
        return $result;
    }


    /**
     * ����� �������� SIM-push
     */
    public function startSimPush($phone)
    {
        $result = $this->request(self::REQUEST_TYPE_SIM_PUSH, [
            "route" => 'pushok',
            "phone" => $phone,
        ]);
        if ($result->isSuccess()) {
            $response = $result->getResult();

            // ��������� �������������
            $result->setMore('EXT_ID', ($data["result"]["id"] ?? null));
        }
        return $result;
    }

    /**
     * ������ ���������
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

        //���� �� ������� ���������� json �����
        if (!$data) {
            return $result->createError(
                '����������� ����� �������',
                'SERVICE_ERROR_UNKNOW'
            );
        }

        // ���� ��������� ������
        if (!$data['success']) {
            return $result->createError(
                sprintf('[%s] %s', $data['error']['code'], $data['error']['descr']),
                'SERVICE_ERROR_CUSTOM'
            );
        }

        // ����� ����� �������� �� ���������� ���������
        $result->setResult($data);

        return $result;
    }


    /**
     * ���������� ������� �� ������ �� ������������, ������������ �� simPush
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
            echo '�� ��� ��������� ������';
            return false;
        }


        if ($md5 !== $this->getMDKey()) {
            echo '�� ������ �������';
            return false;
        }

        $oHistory = new  HistoryTable();


        // ����������� �������� �������. �� �������� ������ � ������, ������� �� ��� ����������.
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
            // �������� ��� ������ ��������
            $this->setUserCallConfirmed($arHistory['ID']);

            // ���� simPush, �������� ��� ������ ������ �� ���������� ������������
            $this->setSimPushConfirmed($arHistory['ID']);
        }

        echo 'OK';

    }


    /**
     * ����� ����������� ����������, ������
     * https://site.ru/bitrix/tools/bxmaker.authuserphone/callback.php?sid={$this->getId()}&md5={$this->getMDKey()};
     * $this->getId() ���������� ������������� �������� �������
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
     * �������� ������� � ������� ������� (���������� � �� �������), ���� ����, ��  ����� �������������� ����������,
     * ���� ��� �������, �� ��� �������� ������� ����� ����������� ��� �� ������ � �������
     *
     * @return bool
     */
    public function hasCallback()
    {
        return false;
    }


}
