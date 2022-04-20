<?php

namespace Only\Rest\Client\Loyalty;


use Exception;

class Cards extends Main
{
    /**
     * @param $cardId
     * @param int $points
     * @param $name
     *
     * @return array
     * @throws \Exception
     * @see http://bonus.olgaberloga.ru:2015/api/swagger/ cards_deposit
     */
    public function deposit($cardId, int $points, $name): array
    {
        $this->contentType = 'JSON';
        $this->method = 'POST';
        $this->url = "{$this->baseUrl}/cards/deposit/";
        $this->arData = [
            "card_no"              => $cardId,
            "payment_bonus_points" => $points,
            "level_bonus_points"   => $points,
            "name"                 => $name,
        ];

        return $this->query();
    }
}
