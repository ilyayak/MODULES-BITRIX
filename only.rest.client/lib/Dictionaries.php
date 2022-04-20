<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 14.11.18
 * Time: 10:36
 */

namespace Only\Rest\Client;


class Dictionaries extends Main
{
    private $dictionaries = [];
    private $result = [];

    protected static $instance = null;

    protected function __construct()
    {
        parent::__construct();
        //$this->dictionaries = json_decode($this->request('GET', 'Dictionaries'), true);
        $this->baseUrl .= 'Dictionaries/';
    }

    private function resultById($id)
    {
        $this->result = $this->request(
            'GET',
            $id . '/'
        );
    }

    private function getFull()
    {
        $this->result = $this->request(
            'GET',
            ''
        );
    }

    public function getList()
    {
        return $this->dictionaries;
    }

    public function getRoomTypes($indexBy = false)
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.RoomType');
        $this->indexing($indexBy);
        return $this->result;
    }

    public function getRooms()
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.Room');
        return $this->result;
    }

    public function getServiceGroups()
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.ServiceGroup');
        return $this->result;
    }

    public function getServices($indexBy = false)
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.ServiceItem');
        $this->indexing($indexBy);
        return $this->result;
    }

    public function getSeasons($indexBy = false)
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.SeasonCode');
        $this->indexing($indexBy);
        return $this->result;
    }

    public function getRates()
    {
        $this->resultById('Logus.HMS.Entities.Dictionaries.Rate');
        return $this->result;
    }

    private function indexing($indexBy)
    {
        if ($indexBy && $this->result && isset(reset($this->result)[$indexBy])) {
            $this->result = array_combine(array_column($this->result, $indexBy), $this->result);
        }
    }

}
