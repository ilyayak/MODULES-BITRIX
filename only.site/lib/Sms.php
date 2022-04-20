<?php

namespace Only\Site;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Sms
{
    private const TOKEN = 'zuswtzgfhi94emk37tag373m6j5ep8t4e95ypcn6jsku8uefs3xifmqr9j3i4uwz';
    private const BASE_URL = 'http://lcab.sms-usluga.ru/json/v1.0';

    /**
     * @var HttpClient
     */
    private $obHttpClient;

    private $arRequest;

    public function __construct($arRequest)
    {
        $this->obHttpClient = new HttpClient();
        $this->obHttpClient->setHeader('Content-Type', 'application/json');
        $this->obHttpClient->setHeader('X-Token', self::TOKEN);

        $this->arRequest = $arRequest;
        $this->arRequest['timeZone'] = 'Asia/Novokuznetsk';
    }

    public function sendText($justCheck = false)
    {
        if (!is_array($this->arRequest)) {
            return ['error' => ['descr' => 'Неверные параметры сообщения']];
        }
        if (empty($this->arRequest['messages'][0]['recipient'])) {
            return ['error' => ['descr' => 'Не указан получатель сообщения']];
        }
        if (empty($this->arRequest['messages'][0]['text'])) {
            return ['error' => ['descr' => 'Нет сообщения']];
        }

        $url = self::BASE_URL . "/sms/send/text";

        if ($justCheck) {
            $this->arRequest['validate'] = true;
        }
        $responseJson = $this->obHttpClient->post($url, Json::encode($this->arRequest));
        return Json::decode($responseJson);
    }
}
