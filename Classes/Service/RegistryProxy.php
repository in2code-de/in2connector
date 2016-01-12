<?php
namespace In2code\In2connector\Service;

/*
 * Copyright notice
 *
 * (c) 2015 Oliver Eglseder <oliver.eglseder@in2code.de>, in2code GmbH
 *
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Registry;

/**
 * Class RegistryProxy
 */
class RegistryProxy extends Registry
{
    /**
     * While this is false, every access has to be done by the proxy
     * If true, then flush all cached stuff by setting it in the original
     * and execute every further request with parent only
     *
     * @var bool
     */
    protected $isTransparent = false;

    /**
     * @var array
     */
    protected $proxyCache = [];

    /**
     * RegistryProxy constructor.
     */
    public function __construct()
    {
        $this->update();
    }

    /**
     * execute at the beginning of every method call
     */
    protected function update()
    {
        if (false === $this->isTransparent) {
            // when the DB is ready then we don't need to proxy anything
            if (true === $this->isDatabaseReady()) {
                foreach ($this->proxyCache as $namespace => $keyValuePair) {
                    foreach ($keyValuePair as $key => $value) {
                        parent::set($namespace, $key, $value);
                    }
                }
                $this->proxyCache = [];
                $this->isTransparent = true;
            }
        }
    }

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
        $this->update();

        if (true === $this->isTransparent) {
            return parent::get($namespace, $key, $defaultValue);
        } else {
            if (isset($this->proxyCache[$namespace][$key])) {
                return $this->proxyCache[$namespace][$key];
            } else {
                return $defaultValue;
            }
        }
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
        $this->update();

        if (true === $this->isTransparent) {
            parent::set($namespace, $key, $value);
        } else {
            $this->validateNamespace($namespace);
            $this->proxyCache[$namespace][$key] = $value;
        }
    }

    /**
     * DO NOTHING
     *
     * @param $namespace
     * @param $key
     */
    public function remove($namespace, $key)
    {
        $this->update();

        if (true === $this->isTransparent) {
            parent::remove($namespace, $key);
        } else {
            $this->validateNamespace($namespace);
            unset($this->proxyCache[$namespace][$key]);
        }
    }

    /**
     * DO NOTHING
     *
     * @param $namespace
     */
    public function removeAllByNamespace($namespace)
    {
        $this->update();

        if (true === $this->isTransparent) {
            parent::removeAllByNamespace($namespace);
        } else {
            $this->validateNamespace($namespace);
            unset($this->proxyCache[$namespace]);
        }
    }

    /**
     * @return bool
     */
    protected function isDatabaseReady()
    {
        $databaseConnection = $this->getDatabaseConnection();
        if (null !== $databaseConnection) {
            return in_array('sys_registry', $databaseConnection->admin_get_tables());
        }
        return false;
    }

    /**
     * @return DatabaseConnection|null
     */
    protected function getDatabaseConnection()
    {
        return !empty($GLOBALS['TYPO3_DB']) ? $GLOBALS['TYPO3_DB'] : null;
    }
}
