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

use In2code\In2connector\Driver\AbstractDriver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DriverRegistration
 */
class DriverRegistration
{
    /**
     * @var string
     */
    protected $driverName = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $settingsPartial = '';

    /**
     * DriverRegistration constructor.
     *
     * @param string $driverName
     * @param string $class
     * @param string $settingsPartial
     */
    public function __construct($driverName, $class, $settingsPartial)
    {
        $this->driverName = $driverName;
        $this->class = $class;
        $this->settingsPartial = $settingsPartial;
    }

    /**
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @param string $driverName
     */
    public function setDriverName($driverName)
    {
        $this->driverName = $driverName;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getSettingsPartial()
    {
        return $this->settingsPartial;
    }

    /**
     * @param string $settingsPartial
     */
    public function setSettingsPartial($settingsPartial)
    {
        $this->settingsPartial = $settingsPartial;
    }

    /**
     * @return AbstractDriver
     */
    public function getDriverInstance()
    {
        return GeneralUtility::makeInstance($this->class);
    }
}
