<?php
namespace In2code\In2connector\Service;

/**
 * Class NullRegistry
 */
class NullRegistry
{
    /**
     * @param $namespace
     * @param $key
     * @param null $defaultValue
     * @return null
     *
     * DO NOTHING
     */
    public function get($namespace, $key, $defaultValue = null)
    {
        return $defaultValue;
    }

    /**
     * DO NOTHING
     *
     * @param $namespace
     * @param $key
     * @param $value
     */
    public function set($namespace, $key, $value)
    {
    }

    /**
     * DO NOTHING
     *
     * @param $namespace
     * @param $key
     */
    public function remove($namespace, $key)
    {
    }

    /**
     * DO NOTHING
     *
     * @param $namespace
     * @param $key
     */
    public function removeAllByNamespace($namespace)
    {
    }
}
