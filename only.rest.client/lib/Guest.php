<?php


namespace Only\Rest\Client;


class Guest extends Main
{
    protected static $instance = null;

    protected function __construct()
    {
        parent::__construct();
        $this->baseUrl .= 'GuestProfile/';
    }

    /**
     * @param array $arCriterias like ['phone' => '8-909-518-33-22']
     * @return array
     */
    public function search(array $arCriterias)
    {
        $arParams = [];
        foreach ($arCriterias as $kCriteria => $vCriteria) {
            $arParams["filter.{$kCriteria}"] = $vCriteria;
        }

        return $this->request(
            'GET',
            'Search',
            $arParams
        )['Items'][0];
    }
}
