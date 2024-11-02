<?php
namespace Hooshina\App;

use Hooshina\App\Connection;

class AiService
{
    public static function use(): Connection
    {
        return new Connection();
    }
}