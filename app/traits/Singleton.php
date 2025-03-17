<?php
namespace HooshinaAi\App\Traits;

trait Singleton
{
    public static $instance = null;

    public static function Instance(): ?Singleton
    {
        if (is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }
}