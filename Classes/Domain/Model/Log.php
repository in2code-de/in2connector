<?php
namespace In2code\In2connector\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Log class to retrieve logged mesasge since there's no API
 * Uses Extbase for minimization of implementation effort
 */
class Log extends AbstractEntity
{
    /**
     * @var string
     */
    protected $requestId = '';

    /**
     * @var float
     */
    protected $timeMicro = 0.0;

    /**
     * @var string
     */
    protected $component = '';

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * The data is a json_encoded array prepended with '- '
     * Have a look at \TYPO3\CMS\Core\Log\LogRecord::__toString
     *
     * @var string
     */
    protected $data = '';

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param string $requestId
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @return float
     */
    public function getTimeMicro()
    {
        return $this->timeMicro;
    }

    /**
     * @param float $timeMicro
     */
    public function setTimeMicro($timeMicro)
    {
        $this->timeMicro = $timeMicro;
    }

    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getDecodedData()
    {
        return json_decode(substr($this->data, 2), true);
    }
}
