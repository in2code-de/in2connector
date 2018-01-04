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
