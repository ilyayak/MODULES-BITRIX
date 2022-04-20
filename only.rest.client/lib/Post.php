<?php

namespace Only\Rest\Client;

class Post extends Main
{
    protected static $instance = null;

    protected function __construct()
    {
        parent::__construct();
        $this->baseUrl .= 'Post/';
    }

    public function post($folioNo, $articleNo, $name, $quantity, $amount)
    {
//        if (!($posts = $params['posts'] ?? [])) return;
//        foreach ($posts as $post) {
//            if (!($folioNo = $post['folioNo'] ?? 0)) continue;
            $response = $this->request(
                'POST',
                '',
                [
                    'FolioNo' => $folioNo,
                    'Orders' => [
                        [
                            'Id' => $folioNo + 3,
                            'Items' => [
                                [
                                    'ArticleNo' => $articleNo,
                                    'Name' => $name,
                                    'Quantity' => $quantity,
                                    'Amount' => $amount,
                                ],
                            ],
                        ],
                    ],
                ]
            );
            return $response;
//        }
    }
}
