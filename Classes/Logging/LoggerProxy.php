<?php
namespace In2code\In2connector\Logging;

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

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Log\Logger;

/**
 * Class LoggerProxy
 */
class LoggerProxy extends Logger
{
    /**
     * @var array
     */
    protected $proxyCache = [];

    /**
     * @var bool
     */
    protected $isTransparent = false;

    /**
     *
     */
    protected function update()
    {
        if (false === $this->isTransparent) {
            if ($this->isDatabaseReady()) {
                foreach ($this->proxyCache as $logEntry) {
                    parent::log($logEntry[0], $logEntry[1], $logEntry[2]);
                }
                $this->proxyCache = [];
                $this->isTransparent = true;
            }
        }
    }

    /**
     * @param int|string $level
     * @param string $message
     * @param array $data
     * @return $this|mixed
     */
    public function log($level, $message, array $data = array())
    {
        $this->update();

        if (true === $this->isTransparent) {
            return parent::log($level, $message, $data);
        } else {
            $this->proxyCache[] = [$level, $message, $data];
            return $this;
        }
    }

    /**
     * @return bool
     */
    protected function isDatabaseReady()
    {
        $databaseConnection = $this->getDatabaseConnection();
        if (null !== $databaseConnection) {
            return in_array('tx_in2connector_log', array_keys($databaseConnection->admin_get_tables()));
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
