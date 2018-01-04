<?php
namespace In2code\In2connector\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015 in2code.de and the following authors:
 * Oliver Eglseder <oliver.eglseder@in2code.de>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException;
use In2code\In2connector\Translation\TranslationTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Connection
 */
class Connection
{
    use TranslationTrait;
    const TEST_RESULT_OK = 0;
    const TEST_RESULT_INFO = 1;
    const TEST_RESULT_WARNING = 2;
    const TEST_RESULT_ERROR = 3;

    /**
     * @var int
     */
    protected $uid = 0;

    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var string
     */
    protected $driver = '';

    /**
     * @var string
     */
    protected $settings = [];

    /**
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = (int)$uid;
    }

    /**
     * @return string
     */
    public function getIdentityKey()
    {
        return $this->identityKey;
    }

    /**
     * @param string $identityKey
     */
    public function setIdentityKey($identityKey)
    {
        $this->identityKey = $identityKey;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return string
     * @throws \In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException
     */
    public function getSettingsPartial()
    {
        $partial = 'Driver/Blank';
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $driverRegistration = $connectionRegistry->getRegisteredDriver($this->driver);
        if (false !== $driverRegistration) {
            $partial = $driverRegistration->getSettingsPartial();
        }
        return $partial;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        if (is_array($this->settings)) {
            return $this->settings;
        } else {
            return json_decode($this->settings, true);
        }
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        if (is_string($settings)) {
            $this->settings = $settings;
        } elseif (is_array($settings)) {
            $this->settings = json_encode($settings);
        }
    }

    /*********************************************
     *
     *                 TESTING
     *
     *********************************************/

    /**
     * @var bool
     */
    protected $testExecuted = false;

    /**
     * @var string
     */
    protected $testResultMessage = '';

    /**
     * @var string
     */
    protected $testResultCode = 0;

    /**
     * @return string
     */
    public function getTestResultMessage()
    {
        return $this->testResultMessage;
    }

    /**
     * @return int
     */
    public function getConnectionTestResult()
    {
        if (false === $this->testExecuted) {
            $this->testResultCode = self::TEST_RESULT_OK;
            if ($this->driver === '') {
                $this->testResultMessage = $this->translate(
                    'domain.model.connection.connection_test.result.no_driver'
                );
                $this->testResultCode = self::TEST_RESULT_ERROR;
            } else {
                $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
                try {
                    $driverRegistration = $connectionRegistry->getRegisteredDriver($this->driver);
                } catch (DriverNameNotRegisteredException $e) {
                    $driverRegistration = false;
                }
                if (!$driverRegistration) {
                    $this->testResultMessage = $this->translate(
                        'domain.model.connection.connection_test.result.driver_not_registered'
                    );
                    $this->testResultCode = self::TEST_RESULT_ERROR;
                } else {
                    $driverInstance = $driverRegistration->getDriverInstance();
                    $driverInstance->setSettings($this->getSettings());
                    if (!$driverInstance->validateSettings()) {
                        $this->testResultMessage = $driverInstance->getLastErrorMessage();
                        $this->testResultCode = self::TEST_RESULT_WARNING;
                    }
                }
            }
        }
        return $this->testResultCode;
    }

    /**
     *
     */
    public function resetTestResult()
    {
        $this->testExecuted = false;
        $this->testResultMessage = '';
    }

    /**
     * @return \In2code\In2connector\Driver\AbstractDriver
     * @throws DriverNameNotRegisteredException
     */
    public function getDriverInstance()
    {
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $driverInstance = $connectionRegistry->getRegisteredDriver($this->driver)->getDriverInstance();
        $driverInstance->setSettings($this->getSettings());
        return $driverInstance;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $properties = array(
            'identity_key' => $this->identityKey,
            'driver' => $this->driver,
            'settings' => json_encode($this->getSettings()),
        );
        if (!empty($this->uid)) {
            $properties['uid'] = $this->uid;
        }
        return $properties;
    }

    /**
     * @param array $properties
     * @return static
     */
    public static function fromArray(array $properties)
    {
        $connection = new static();
        if (isset($properties['uid'])) {
            $connection->setUid($properties['uid']);
        }
        $connection->setSettings($properties['settings']);
        $connection->setDriver($properties['driver']);
        if (isset($properties['identity_key'])) {
            $identityKey = $properties['identity_key'];
        } elseif (isset($properties['identityKey'])) {
            $identityKey = $properties['identityKey'];
        } else {
            $identityKey = '';
        }
        $connection->setIdentityKey($identityKey);
        return $connection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->uid;
    }
}
