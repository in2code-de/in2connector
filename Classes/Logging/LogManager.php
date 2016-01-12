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

use TYPO3\CMS\Core\Log\LogManager as CoreLogManager;

/**
 * Class LogManager
 */
class LogManager extends CoreLogManager
{
    /**
     * @param string $name
     * @return LoggerProxy
     */
    public function getLogger($name = '')
    {
        $name = str_replace(['_', '\\'], '.', $name);

        if (isset($this->loggers[$name])) {
            $logger = $this->loggers[$name];
        } else {
            $logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LoggerProxy::class, $name);
            $this->loggers[$name] = $logger;
            $this->setWritersForLogger($logger);
            $this->setProcessorsForLogger($logger);
        }
        return $logger;
    }
}
