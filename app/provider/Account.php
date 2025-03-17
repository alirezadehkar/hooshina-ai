<?php
namespace HooshinaAi\App\Provider;

use HooshinaAi\App\Generator\GeneratorAbstract;
use HooshinaAi\App\Provider\HaiClient;

class Account extends GeneratorAbstract 
{
    public function get_balance()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('user/wallet/balance');

        return $response ? $this->find($response, 'data') : null;
    }

    public function balance_sufficient()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('user/wallet/balance/sufficient');

        $data = $this->find($response, 'data');

        return is_array($data) && isset($data['value']) && $data['value'] == true;
    }
}