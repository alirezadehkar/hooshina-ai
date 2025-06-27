<?php
namespace HooshinaAi\App\Generator;

use HooshinaAi\App\Uploader;

abstract class GeneratorAbstract
{
    protected $params = [];

    public function set_params($params){
        $this->params = $params;
        return $this;
    }

    public function get_param($key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function find($object_or_array, $key)
    {
        if (is_object($object_or_array)) {
            $object_or_array = (array) $object_or_array;
        }

        $stack = [$object_or_array];

        while ($stack) {
            $current = array_pop($stack);

            if (is_array($current) && array_key_exists($key, $current)) {
                return $current[$key];
            }

            foreach ($current as $item) {
                if (is_array($item) || is_object($item)) {
                    $stack[] = (array) $item;
                }
            }
        }

        return null;
    }

    public function find_last($object_or_array, $key)
    {
        if (is_object($object_or_array)) {
            $object_or_array = (array) $object_or_array;
        }

        $stack = [$object_or_array];
        $lastValue = null;

        while ($stack) {
            $current = array_pop($stack);

            if (is_array($current) && array_key_exists($key, $current)) {
                $lastValue = $current[$key];
            }

            foreach ($current as $item) {
                if (is_array($item) || is_object($item)) {
                    $stack[] = (array) $item;
                }
            }
        }

        return $lastValue;
    }

    protected function uploadFile($url)
    {
        $uploader = new Uploader($url);
        return $uploader->uploadWithUrl();
    }
}