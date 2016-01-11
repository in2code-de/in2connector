<?php
namespace In2code\In2connector\Domain\Model\Dto;

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

use In2code\In2connector\Service\ConfigurationService;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * Class Configuration
 */
class Configuration
{
    /**
     * @var int
     */
    protected $logLevel = LogLevel::DEBUG;

    /**
     * @var int
     */
    protected $logsPerPage = ConfigurationService::DEFAULT_LOGS_PER_PAGE;

    /**
     * @var bool
     */
    protected $productionContext = ConfigurationService::DEFAULT_PRODUCTION_CONTEXT;

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param int $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @return int
     */
    public function getLogsPerPage()
    {
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
        return $this->productionContext;
    }

    /**
     * @param boolean $productionContext
     */
    public function setProductionContext($productionContext)
    {
        $this->productionContext = $productionContext;
    }
}
