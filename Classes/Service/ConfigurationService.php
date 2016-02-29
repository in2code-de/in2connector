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

use In2code\In2connector\Domain\Model\Dto\Configuration;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Wraps the TYPO3 registry for convenient usage to retrieve special values fast and easily
 */
class ConfigurationService implements SingletonInterface
{
    const DEFAULT_LOGS_PER_PAGE = 25;
    const DEFAULT_PRODUCTION_CONTEXT = true;
    const DEFAULT_LOG_LEVEL = LogLevel::DEBUG;
    const LOG_LEVEL = 'log_level';
    const LOGS_PER_PAGE = 'logs_per_page';
    const PRODUCTION_CONTEXT = 'production_context';

    /**
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var int
     */
    protected $logLevel = null;

    /**
     * @var int
     */
    protected $logsPerPage = null;

    /**
     * @var bool
     */
    protected $productionContext = null;

    /**
     * ConfigurationService constructor.
     */
    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * @return Configuration
     */
    public function getConfigurationDto()
    {
        $configuration = new Configuration();
        return $configuration->setLogLevel($this->getLogLevel())
                             ->setLogsPerPage($this->getLogsPerPage())
                             ->setProductionContext($this->isProductionContext());
    }

    /**
     * @param Configuration $configuration
     */
    public function updateFromConfigurationDto(Configuration $configuration)
    {
        $this->setLogLevel($configuration->getLogLevel());
        $this->setLogsPerPage($configuration->getLogsPerPage());
        $this->setProductionContext($configuration->isProductionContext());
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        if (null === $this->logLevel) {
            if ($this->isDatabaseReady()) {
                $this->loadFromDatabase();
            } else {
                return self::DEFAULT_LOG_LEVEL;
            }
        }
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        if (($logLevel >= LogLevel::EMERGENCY) && ($logLevel <= LogLevel::DEBUG)) {
            $this->logLevel = $logLevel;
        }
    }

    /**
     * @return int
     */
    public function getLogsPerPage()
    {
        if (null === $this->logsPerPage) {
            if ($this->isDatabaseReady()) {
                $this->loadFromDatabase();
            } else {
                return self::DEFAULT_LOGS_PER_PAGE;
            }
        }
        return $this->logsPerPage;
    }

    /**
     * @param int $logsPerPage
     */
    public function setLogsPerPage($logsPerPage)
    {
        $this->logsPerPage = $logsPerPage;
    }

    /**
     * @return boolean
     */
    public function isProductionContext()
    {
        if (null === $this->productionContext) {
            if ($this->isDatabaseReady()) {
                $this->loadFromDatabase();
            } else {
                return self::DEFAULT_PRODUCTION_CONTEXT;
            }
        }
        return $this->productionContext;
    }

    /**
     * @param boolean $productionContext
     */
    public function setProductionContext($productionContext)
    {
        $this->productionContext = $productionContext;
    }

    /**
     *
     */
    protected function loadFromDatabase()
    {
        $this->logLevel = $this->registry->get(TX_IN2CONNECTOR, self::LOG_LEVEL, $this->logLevel);
        $this->logsPerPage = $this->registry->get(TX_IN2CONNECTOR, self::LOGS_PER_PAGE, $this->logsPerPage);
        $this->productionContext = $this->registry->get(
            TX_IN2CONNECTOR,
            self::PRODUCTION_CONTEXT,
            $this->productionContext
        );
    }

    /**
     * Ensure to write all cached stuff back to the registry
     */
    public function __destruct()
    {
        if ($this->isDatabaseReady()) {
            if (null !== $this->logLevel) {
                $this->registry->set(TX_IN2CONNECTOR, self::LOG_LEVEL, $this->logLevel);
            }
            if (null !== $this->logsPerPage) {
                $this->registry->set(TX_IN2CONNECTOR, self::LOGS_PER_PAGE, $this->logsPerPage);
            }
            if (null !== $this->productionContext) {
                $this->registry->set(TX_IN2CONNECTOR, self::PRODUCTION_CONTEXT, $this->productionContext);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isDatabaseReady()
    {
        $databaseConnection = $this->getDatabaseConnection();
        if (null !== $databaseConnection) {
            return in_array('sys_registry', array_keys($databaseConnection->admin_get_tables()));
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
