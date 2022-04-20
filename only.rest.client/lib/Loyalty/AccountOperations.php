<?php


namespace Only\Rest\Client\Loyalty;


use Exception;

class AccountOperations extends Main
{
    /**
     * @param array $arFilter ['account' => '2813', 'limit' => 100, 'offset' => 0]
     * @return array
     * @throws Exception
     *
     * "count": 1492,
     * "next": "http://bonus.olgaberloga.ru:2015/api/account-operations/?limit=20&offset=20",
     * "previous": null,
     * "results": [
     * {
     * "id": 761,
     * "point_of_sale": 1,
     * "account": 2735,
     * "external_id": null,
     * "name": "Добро пожаловать: 137815 Исаков Алексей Владимирович",
     * "debit": "851.00",
     * "credit": "0.00",
     * "debit_expirable": "0.00",
     * "credit_expirable": "0.00",
     * "expiration_date": null,
     * "order": 3364,
     * "rule": 5,
     * "bonus_type": 1,
     * "bonus_class": "PAYM",
     * "initiator_user": null,
     * "date_created": "2020-02-17T13:59:19.535351+03:00",
     * "date_modified": "2020-03-07T15:22:52.333902+03:00"
     * },
     * ...]
     */
    public function getList(array $arFilter)
    {
        $query = http_build_query($arFilter);

        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/account-operations/?{$query}";

        return $this->query();
    }
}
