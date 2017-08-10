<?php
/**
 * Slim Framework (https://slimframework.com)
 *
 * @link      https://github.com/slimphp/Slim-Http
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim-Http/blob/master/LICENSE (MIT License)
 */
namespace Core\Helpers;


/**
 * Collection
 *
 * This class provides a common interface used by many other
 * classes in a Slim application that manage "collections"
 * of data that must be inspected and/or manipulated
 */
class Collection
{
    /**
     * The source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create new collection
     *
     * @param array $items Pre-populate collection with this key-value array
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }


    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Get collection item for key
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * Add item to collection
     *
     * @param array $items Key-value array of data to append to this collection
     */
    public function replace(array $items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get all items in collection
     *
     * @return array The collection's source data
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Get collection keys
     *
     * @return array The collection's source data keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Remove all items from collection
     */
    public function clear()
    {
        $this->data = [];
    }

}
