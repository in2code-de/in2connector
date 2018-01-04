<?php
namespace In2code\In2connector\Service;

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

use In2code\In2connector\Domain\Model\Connection;
use In2code\In2connector\Domain\Repository\ConnectionRepository;
use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Service\Exceptions\ConnectionInvalidException;
use In2code\In2connector\Service\Exceptions\ConnectionNeverDemandedException;
use In2code\In2connector\Service\Exceptions\ConnectionNotConfiguredException;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ConnectionService
 */
class ConnectionService implements SingletonInterface
{
    /**
     * @var ConnectionRegistry
     */
    protected $connectionRegistry = null;

    /**
     * @var ConfigurationService
     */
    protected $configurationService = null;

    /**
     * @var ConnectionRepository
     */
    protected $connectionRepository = null;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * ConnectionService constructor.
     */
    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(get_class($this));
        $this->connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->connectionRepository = $objectManager->get(ConnectionRepository::class);
    }

    /**
     * @param string $identityKey
     * @return bool
     */
    public function hasConnection($identityKey)
    {
        return true === $this->connectionRegistry->hasDemandedConnection($identityKey)
               && 1 === $this->connectionRepository->countByIdentityKey($identityKey)
               && (($connection = $this->connectionRepository->findOneByIdentityKey($identityKey)) instanceof
                   Connection)
               && ($connection::TEST_RESULT_OK === $connection->getConnectionTestResult());
    }

    /**
     * @param string $identityKey
     * @return \In2code\In2connector\Domain\Model\Connection
     * @throws ConnectionInvalidException
     * @throws ConnectionNeverDemandedException
     * @throws ConnectionNotConfiguredException
     */
    public function getConnection($identityKey)
    {
        $isProduction = $this->configurationService->isProductionContext();
        if (!$this->connectionRegistry->hasDemandedConnection($identityKey)) {
            $message = sprintf('Requested connection with identityKey "%s" was never demanded', $identityKey);
            if ($isProduction) {
                $this->logger->alert($message);
                return false;
            } else {
                throw new ConnectionNeverDemandedException($message, 1452856398);
            }
        }
        if (1 !== ($count = $this->connectionRepository->countByIdentityKey($identityKey))) {
            $message = sprintf(
                'Requested connection with identityKey "%s", but it was found %d times',
                $identityKey,
                $count
            );
            if ($isProduction) {
                $this->logger->alert($message);
                return false;
            } else {
                throw new ConnectionNotConfiguredException($message, 1452856469);
            }
        }
        $connection = $this->connectionRepository->findOneByIdentityKey($identityKey);
        if ($connection::TEST_RESULT_OK !== ($tesResultCode = $connection->getConnectionTestResult())) {
            $message = sprintf(
                'The connection "%s" is invalid (Connection test failed: [%d] "%s")',
                $identityKey,
                $tesResultCode,
                $connection->getTestResultMessage()
            );
            if ($isProduction) {
                $this->logger->alert($message);
                return false;
            } else {
                throw new ConnectionInvalidException($message, 1452857678);
            }
        }
        return $connection;
    }

    /**
     * @param string $identityKey
     * @return bool|Connection Returns the desired connection if it is available, otherwise false
     * @throws ConnectionInvalidException
     * @throws ConnectionNeverDemandedException
     * @throws ConnectionNotConfiguredException
     */
    public function getConnectionIfAvailable($identityKey)
    {
        if ($this->hasConnection($identityKey)) {
            return $this->getConnection($identityKey);
        } else {
            return false;
        }
    }

    /**
     * @param string $identityKey Returns the desired connection's driver if it is available, otherwise false
     * @return bool|\In2code\In2connector\Driver\AbstractDriver
     */
    public function getDriverInstanceIfAvailable($identityKey)
    {
        $connection = $this->getConnectionIfAvailable($identityKey);
        if ($connection instanceof Connection) {
            return $connection->getDriverInstance();
        } else {
            return false;
        }
    }
}
