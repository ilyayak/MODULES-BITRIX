<?php

namespace Only\Rest\Client\Loyalty;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Exception;


class Main
{
    const MODULE_ID = 'only.rest.client';

    /**
     * @var HttpClient
     */
    protected $httpClient;

    private $token;
    private $iAttempt = 0;

    protected $baseUrl;
    protected $method;
    protected $url;
    protected $contentType = 'FORM';
    protected $followRedirect = true;
    protected $arData = [];
    protected $arRequiredFields = [];

    public function __construct()
    {
        $this->httpClient = new HttpClient();
        $this->httpClient->setHeader('Accept', 'application/json');

        $this->token = Option::get(self::MODULE_ID, 'loyalty_token');
        $this->baseUrl = Option::get(self::MODULE_ID, 'loyalty_base_url');
        $this->baseUrl = rtrim($this->baseUrl, '/');

        if (!$this->token) {
            $this->auth();
        } else {
            $this->httpClient->setHeader('Authorization', "Bearer {$this->token}");
        }
    }

    private function auth(): void
    {
        $arAuth = [
            'username' => Option::get(self::MODULE_ID, 'loyalty_user'),
            'password' => Option::get(self::MODULE_ID, 'loyalty_password'),
        ];

        $obClient = new HttpClient();
        $obClient->setHeader('Content-Type', 'application/json');
        $arAuth = Json::encode($arAuth);
        $responseJson = $obClient->post("{$this->baseUrl}/auth/token/", $arAuth);
        $arResponse = Json::decode($responseJson);

        if (isset($arResponse['access'])) {
            $this->token = $arResponse['access'];
            Option::set(self::MODULE_ID, 'loyalty_token', $this->token);
            $this->httpClient->setHeader('Authorization', "Bearer {$this->token}");
        } else {
            throw new Exception("001. Система лояльности недоступна");
        }
    }

    protected function query(): array
    {
        if (!$this->method || !$this->url) {
            throw new Exception("000. Неверный запрос");
        }

        $this->httpClient->setRedirect($this->followRedirect);

        if ($this->contentType == 'JSON') {
            $this->httpClient->setHeader('Content-Type', 'application/json');
            $this->arData = Json::encode($this->arData);
        }

        $this->httpClient->query($this->method, $this->url, $this->arData);

        $arResponse = [];
        $responseStatus = $this->httpClient->getStatus();
        //dtf([$this->method, $this->url, $responseStatus, $this->arData, $this->httpClient->getResult()]);
        if ($responseStatus < 204) {
            $decodedResult = jsonDecodeWithValidate($this->httpClient->getResult());
            $arResponse = is_array($decodedResult) ? $decodedResult : [$decodedResult];
        }

        if (empty($arResponse) || ($arResponse['code'] == 'token_not_valid')) {
            if (++$this->iAttempt < 3) {
                $this->auth();
                $arResponse = $this->query();
            } else {
                throw new Exception("002. Система лояльности недоступна");
            }
        }

        return $arResponse;
    }

    protected function validate(): string
    {
        $arErrors = [];
        foreach (array_keys($this->arRequiredFields) as $requiredField) {
            if (!$this->arData[$requiredField]) {
                $arErrors[] = $this->arRequiredFields[$requiredField];
            }
        }
        return implode(', ', $arErrors);
    }
}
