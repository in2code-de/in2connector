<?php
namespace In2code\In2connector\Service;

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

use In2code\In2connector\Domain\Model\Connection;
use In2code\In2connector\Domain\Repository\ConnectionRepository;
use In2code\In2connector\Logging\LoggerTrait;
use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Service\Exceptions\ConnectionInvalidException;
use In2code\In2connector\Service\Exceptions\ConnectionNeverDemandedException;
use In2code\In2connector\Service\Exceptions\ConnectionNotConfiguredException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ConnectionService
 */
class ConnectionService implements SingletonInterface
{
    use LoggerTrait;

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
     * ConnectionService constructor.
     */
    public function __construct()
    {
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
                $this->getLogger()->alert($message);
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
                $this->getLogger()->alert($message);
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
                $this->getLogger()->alert($message);
                return false;
            } else {
                throw new ConnectionInvalidException($message, 1452857678);
            }
        }
        return $connection;
    }
}
