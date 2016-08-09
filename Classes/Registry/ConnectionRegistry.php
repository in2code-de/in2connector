<?php
namespace In2code\In2connector\Registry;

/*
 * Copyright notice
 *
 * (c) 2015-2016 Oliver Eglseder <oliver.eglseder@in2code.de>, in2code GmbH
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

use In2code\In2connector\Domain\Model\Dto\ConnectionDemand;
use In2code\In2connector\Domain\Model\Dto\DriverRegistration;
use In2code\In2connector\Driver\AbstractDriver;
use In2code\In2connector\Registry\Exceptions\ConnectionAlreadyDemandedException;
use In2code\In2connector\Registry\Exceptions\DriverDoesNotExistException;
use In2code\In2connector\Registry\Exceptions\DriverNameAlreadyRegisteredException;
use In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException;
use In2code\In2connector\Registry\Exceptions\InvalidDriverException;
use In2code\In2connector\Service\ConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConnectionRegistry
 */
class ConnectionRegistry implements SingletonInterface
{
    /**
     * @var \In2code\In2connector\Service\ConfigurationService
     */
    protected $configurationService = null;

    /**
     * @var DriverRegistration[]
     */
    protected $registeredDrivers = [];

    /**
     * @var ConnectionDemand[]
     */
    protected $demandedConnections = [];

    /**
     * ConnectionRegistry constructor.
     */
    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * @param string $driverName
     * @param string $class
     * @param string $settingsPartial
     * @return bool
     * @throws DriverNameAlreadyRegisteredException
     * @throws InvalidDriverException
     */
    public function registerDriver($driverName, $class, $settingsPartial)
    {
        if (isset($this->registeredDrivers[$driverName])) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new DriverNameAlreadyRegisteredException(
                    'The driver name "' . $driverName . '" was already registered',
                    1451926330
                );
            }
        }

        if (!is_subclass_of($class, AbstractDriver::class)) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new InvalidDriverException(
                    'The driver class "' . $class
                    . '" is not a valid driver. It must inherit from \In2code\In2connector\Driver\AbstractDriver',
                    1451926497
                );
            }
        }

        $this->registeredDrivers[$driverName] = new DriverRegistration($driverName, $class, $settingsPartial);
        return true;
    }

    /**
     * @param string $driverName
     * @return bool
     * @throws DriverNameNotRegisteredException
     */
    public function deregisterDriver($driverName)
    {
        if (!isset($this->registeredDrivers[$driverName])) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new DriverNameNotRegisteredException(
                    'The driver name "' . $driverName . '" was never registered',
                    1451927180
                );
            }
        }

        unset($this->registeredDrivers[$driverName]);
        return true;
    }

    /**
     * @param string $identityKey
     * @param string $driverName
     * @return bool
     * @throws ConnectionAlreadyDemandedException
     * @throws DriverDoesNotExistException
     */
    public function demandConnection($identityKey, $driverName)
    {
        if (isset($this->demandedConnections[$identityKey])) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new ConnectionAlreadyDemandedException(
                    'The connection with the identity key "' . $identityKey . '" was already demanded',
                    1451927594
                );
            }
        }

        if (!isset($this->registeredDrivers[$driverName])) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new DriverDoesNotExistException(
                    'The requested driver for the identity key "' . $identityKey . '" was not registered',
                    1451927594
                );
            }
        }

        $this->demandedConnections[$identityKey] = new ConnectionDemand($identityKey, $driverName);
        return true;
    }

    /**
     * @return DriverRegistration[]
     */
    public function getRegisteredDrivers()
    {
        return $this->registeredDrivers;
    }

    /**
     * @param string $driverName
     * @return bool|DriverRegistration
     * @throws DriverNameNotRegisteredException
     */
    public function getRegisteredDriver($driverName)
    {
        if (!isset($this->registeredDrivers[$driverName])) {
            if ($this->configurationService->isProductionContext()) {
                return false;
            } else {
                throw new DriverNameNotRegisteredException(
                    'The driver name "' . $driverName . '" was not registered',
                    1451992063
                );
            }
        }

        return $this->registeredDrivers[$driverName];
    }

    /**
     * @return ConnectionDemand[]
     */
    public function getDemandedConnections()
    {
        return $this->demandedConnections;
    }

    /**
     * @param string $identityKey
     * @return bool
     */
    public function hasDemandedConnection($identityKey)
    {
        return isset($this->demandedConnections[$identityKey]);
    }
}
