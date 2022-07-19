<?php

namespace app\service;

class BaseService
{
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }
        static::$instance = new static();
        return static::$instance;
    }

    // 限制只能通过getInstance生成实例
    private function __construct() {}
    private function __clone() {}
}