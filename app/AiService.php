<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Connection;

class AiService
{
    public static function use(): Connection
    {
        return new Connection();
    }
}