<?php

/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 08.11.18
 * Time: 10:38
 */

namespace Only\Rest\Client;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;


abstract class Main
{
    const MODULE_ID = 'only.rest.client';
    const TIME_DAY_BEGIN = '+14 hours';
    const TIME_DAY_END = '+12 hours';

    protected static $instance = null;
    protected $authData = [];
    protected $baseUrl;
    private $auth_attempt = 0;

    /**
     * Gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Try to login in Logus. Trying three times
     *
     * @return bool
     */
    public function auth(): bool
    {
        if ($this->auth_attempt++ < 3) {
            $oldUrl = $this->baseUrl;
            $this->baseUrl = preg_replace('/\/[^\/]+\/$/', '/', $this->baseUrl);
            $response = $this->request(
                'GET',
                'Account',
                [
                    'userName' => $this->authData['user'],
                    'password' => $this->authData['password'],
                ]
            );
            $this->baseUrl = $oldUrl;
            if ($token = $response['Token'] ?? '') {
                try {
                    Option::set(self::MODULE_ID, 'token', $token);
                } catch (ArgumentOutOfRangeException $exception) {
                    return false;
                }
                $this->authData['token'] = $token;
                return true;
            }
        }
        return false;
    }

    /**
     * Send request to Logus. If failed, try reauthorize and send again.
     *
     * @param string $method
     * @param string $action
     * @param array $params
     * @param bool $duplicateParams
     *
     * @return array
     */
    protected function request($method, $action, $params = [], $duplicateParams = false): array
    {
        $url = $this->baseUrl . $action;

        $isPost = strtoupper($method) == 'POST';
        $ch = curl_init();
        if ($params) {
            if ($isPost)
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            if (!$isPost || $duplicateParams)
                $url .= '?' . http_build_query($params);
        }
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($action != 'Account') $headers[] = 'Authorization: Token ' . $this->authData['token'];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, $isPost);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");

        $response = curl_exec($ch);
        curl_close($ch);

        if (!($array = json_decode($response, true))
            && $action != 'Account' && $this->auth()) {
            $array = $this->request($method, $action, $params, $duplicateParams);
        }

        return $array ?? [];
    }

    /**
     * Is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    protected function __construct()
    {
        Loader::includeModule(self::MODULE_ID);
        $this->baseUrl = Option::get(self::MODULE_ID, 'base_url');
        $this->authData['user'] = Option::get(self::MODULE_ID, 'user');
        $this->authData['password'] = Option::get(self::MODULE_ID, 'password');
        $this->authData['token'] = Option::get(self::MODULE_ID, 'token');
    }

    /**
     * Prevent the instance from being cloned (which would create a second instance of it)
     */
    protected function __clone()
    {
    }

    /**
     * Prevent from being unserialized (which would create a second instance of it)
     */
    protected function __wakeup()
    {
    }
}
