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

use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Rewritten to access values from extConf instead of registry, because configuration might accessed before the
 * database is ready
 */
class ConfigurationService implements SingletonInterface
{
    const LOG_LEVEL = 'log_level';
    const LOG_LEVEL_DEFAULT = LogLevel::DEBUG;
    const LOGS_PER_PAGE = 'logs_per_page';
    const LOGS_PER_PAGE_DEFAULT = 25;
    const PRODUCTION_CONTEXT = 'production_context';
    const PRODUCTION_CONTEXT_DEFAULT = true;

    /**
     * Default configuration which gets overwritten on construction
     *
     * @var array
     */
    protected $configuration = array(
        self::LOG_LEVEL => self::LOG_LEVEL_DEFAULT,
        self::PRODUCTION_CONTEXT => self::PRODUCTION_CONTEXT_DEFAULT,
        self::LOGS_PER_PAGE => self::LOGS_PER_PAGE_DEFAULT,
    );

    /**
     * ConfigurationService constructor.
     */
    public function __construct()
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $this->readConfiguration());
    }

    /**
     * @return array
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    protected function readConfiguration()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['in2connector'])) {
            return (array)unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['in2connector']);
        }
        return array();
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->configuration[self::LOG_LEVEL];
    }

    /**
     * @return int
     */
    public function getLogsPerPage()
    {
        return $this->configuration[self::LOGS_PER_PAGE];
    }

    /**
     * @return boolean
     */
    public function isProductionContext()
    {
        return $this->configuration[self::PRODUCTION_CONTEXT];
    }
}
