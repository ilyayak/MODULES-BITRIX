<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 14.11.18
 * Time: 10:36
 */

namespace Only\Rest\Client;


class Services extends Main
{
    protected static $instance = null;

    protected function __construct()
    {
        parent::__construct();
        $this->baseUrl .= 'Folio/PostingGroups/';
    }

    public function servicesInfo()
    {
        $response = $this->request(
            'GET',
            '',
            []);
        return $response;
    }

}
