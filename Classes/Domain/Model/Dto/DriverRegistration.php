<?php
namespace In2code\In2connector\Domain\Model\Dto;

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
