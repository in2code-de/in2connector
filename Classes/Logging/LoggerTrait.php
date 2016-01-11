<?php
namespace In2code\In2connector\Logging;

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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LoggerTrait
 */
trait LoggerTrait
{
    /**
     * You should always use $this->getLogger() instead of $this->logger, because it might not be secured, that the
     * logger was initialized already
     *
     * @var Logger
     */
    protected $logger = null;

    /**
     * @return Logger
     * @api
     */
    protected function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(get_class($this));
        }
        return $this->logger;
    }
}
