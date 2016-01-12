<?php
namespace In2code\In2connector\Domain\Model\Dto;

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

/**
 * Class ConnectionDemand
 */
class ConnectionDemand
{
    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var string
     */
    protected $driverName = '';

    /**
     * ConnectionDemand constructor.
     *
     * @param string $identityKey
     * @param string $driverName
     */
    public function __construct($identityKey, $driverName)
    {
        $this->identityKey = $identityKey;
        $this->driverName = $driverName;
    }

    /**
     * @return string
     */
    public function getIdentityKey()
    {
        return $this->identityKey;
    }

    /**
     * @param string $identityKey
     */
    public function setIdentityKey($identityKey)
    {
        $this->identityKey = $identityKey;
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
}
