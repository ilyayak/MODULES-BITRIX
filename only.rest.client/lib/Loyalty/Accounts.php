<?php


namespace Only\Rest\Client\Loyalty;


class Accounts extends Main
{
    public function getList()
    {
        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/accounts/";

        return $this->query();
    }

    public function getById($id)
    {
        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/accounts/{$id}/";

        return $this->query();
    }

    public function getOperations($accountId)
    {
        $this->followRedirect = false;
        $this->method = 'GET';
        $this->url = "{$this->baseUrl}/accounts/{$accountId}/operations/";

        return $this->query();
    }
}
