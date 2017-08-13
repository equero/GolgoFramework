<?php
namespace Core\Controllers\Http\Psr\Interfaces;

/**
 * Cookies Interface
 *
 * @package Slim
 * @since   1.0.0
 */
interface CookiesInterface
{
    public function get($name, $default = null);
    public function set($name, $value);
    public function toHeaders();
    public static function parseHeader($header);
}
