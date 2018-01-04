<?php
namespace In2code\In2connector\Driver;

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

use In2code\In2connector\Driver\Exception\ErrorException;
use In2code\In2connector\Service\ConfigurationService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractDriver
 */
abstract class AbstractDriver
{
    /**
     * @var ConfigurationService
     */
    protected $configurationService = null;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $lastErrorCode = 0;

    /**
     * @var string
     */
    protected $lastErrorMessage = '';

    /**
     * @var bool
     */
    private static $errorCapturingEnabled = false;

    /**
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * AbstractDriver constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(get_class($this));
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->setSettings($settings);
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings = null)
    {
        if (null !== $settings) {
            $this->settings = $settings;
        }
    }

    /**
     * @return string
     */
    public function getLastErrorCode()
    {
        return $this->lastErrorCode;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @return bool
     */
    abstract public function validateSettings();

    /**
     * @param bool $start
     */
    protected function captureErrors($start)
    {
        if (true === $start && false === self::$errorCapturingEnabled) {
            set_error_handler([$this, 'captureError']);
            self::$errorCapturingEnabled = true;
        } elseif (false === $start && true === self::$errorCapturingEnabled) {
            restore_error_handler();
            self::$errorCapturingEnabled = false;
        }
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws ErrorException
     */
    public function captureError($errorCode, $errorMessage, $file, $line, $context = [])
    {
        if ($errorCode === E_NOTICE) {
            return;
        }
        if ($this->investigateError($errorCode, $errorMessage, $file, $line, $context)) {
            return;
        } else {
            if ($this->configurationService->isProductionContext()) {
                $this->logger->error(
                    $errorMessage,
                    ['errorCode' => $errorCode, 'errorMessage' => $errorMessage, 'file' => $file, 'line' => $line]
                );
            } else {
                throw new ErrorException($errorMessage, 1452002476);
            }
        }
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $file
     * @param int $line
     * @param array $context
     * @return bool Returns true if the error was investigated and can be ignored, false if it should be logged and
     *     reported
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function investigateError($errorCode, $errorMessage, $file, $line, $context)
    {
        return false;
    }

    /**
     * @return false
     */
    abstract public function fetchErrors();

    /**
     * @return string
     */
    abstract public function getErrors();

    /**
     * @return true
     */
    abstract public function resetErrors();

    /**
     * @return bool
     */
    abstract public function hasErrors();
}
