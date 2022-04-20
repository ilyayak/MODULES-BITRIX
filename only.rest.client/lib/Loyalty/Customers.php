<?php


namespace Only\Rest\Client\Loyalty;


use Bitrix\Main\Diag\Debug;
use Exception;

class Customers extends Main
{
    /**
     * @param array $arFilter ['phone' => '+79516005235']
     * @return array
     * @throws Exception
     *
     * @see http://bonus.olgaberloga.ru:2015/api/swagger/ customers_list
     */
    public function getList(array $arFilter): array
    {
        $query = http_build_query($arFilter);

        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/customers/?{$query}";

        return $this->query();
    }


    /**
     * @param $id
     * @return array
     * @throws Exception
     *
     * @see http://bonus.olgaberloga.ru:2015/api/swagger/ customers_read
     */
    public function getById($id = 0): array
    {
        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/customers/{$id}/";

        return $this->query();
    }

    /**
     * @param array $arData
     * @return array
     * @throws Exception
     *
     * @see http://bonus.olgaberloga.ru:2015/api/swagger/ customers_create
     */
    public function create(array $arData): array
    {
        $this->contentType = 'JSON';
        $this->method = 'POST';
        $this->url = "{$this->baseUrl}/customers/";
        $this->arData = $arData;

        $this->arRequiredFields = [
            'phone'      => 'Телефон',
            'first_name' => 'Имя',
            'last_name'  => 'Фамилия',
            'birth_date' => 'Дата рождения',
        ];
        if ($sErrors = $this->validate()) {
            throw new Exception("010. Заполните следующие поля: {$sErrors}.");
        }

        return $this->query();
    }

    /**
     * @param $userId
     * @param array $arData
     * @return array
     * @throws Exception
     * @see http://bonus.olgaberloga.ru:2015/api/swagger/ customers_partial_update
     */
    public function update($userId, array $arData): array
    {
        $this->contentType = 'JSON';
        $this->method = 'PATCH';
        $this->url = "{$this->baseUrl}/customers/{$userId}/";
        $this->arData = $arData;

        return $this->query();
    }
}
