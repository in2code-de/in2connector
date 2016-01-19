<?php
namespace In2code\In2connector\Driver;

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

use In2code\In2connector\Translation\TranslationTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SoapDriver
 */
class SoapDriver extends AbstractDriver
{
    use TranslationTrait;
    const TEST_WSDL_FILE_EMPTY = 300;
    const TEST_WSDL_FILE_NOT_READABLE = 301;
    const TEST_VERSION_EMPTY = 302;
    const TEST_UNRECOGNIZED_VERSION = 303;
    const TEST_COMPRESSION_NOT_SET = 304;
    const TEST_ENCODING_NOT_SET = 305;
    const TEST_SOAP_ERROR = 306;

    /**
     * @var \SoapClient
     */
    protected $soapClient = null;

    /**
     * @return bool
     */
    public function validateSettings()
    {
        if (empty($this->settings['wsdlFile'])) {
            $this->lastErrorCode = self::TEST_WSDL_FILE_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.soap.test.wsdl_file_empty');
            return false;
        }

        $options = [];

        if (empty($this->settings['version'])) {
            $this->lastErrorCode = self::TEST_VERSION_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.soap.test.version_empty');
            return false;
        }

        switch ($this->settings['version']) {
            case '1001':
                $options['soap_version'] = SOAP_1_1;
                break;
            case '1002':
                $options['soap_version'] = SOAP_1_2;
                break;
            default:
                $this->lastErrorCode = self::TEST_UNRECOGNIZED_VERSION;
                $this->lastErrorMessage = $this->translate('driver.soap.test.version_not_recognized');
                return false;
        }

        if (!empty($this->settings['username'])) {
            $options['login'] = $this->settings['username'];
        }

        if (!empty($this->settings['password'])) {
            $options['password'] = $this->settings['password'];
        }

        if (!isset($this->settings['compression'])) {
            $this->lastErrorCode = self::TEST_COMPRESSION_NOT_SET;
            $this->lastErrorMessage = $this->translate('driver.soap.test.compression_not_set');
            return false;
        } elseif (true === (bool)$this->settings['compression']) {
            $options['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        }

        if (!isset($this->settings['encoding'])) {
            $this->lastErrorCode = self::TEST_ENCODING_NOT_SET;
            $this->lastErrorMessage = $this->translate('driver.soap.test.encoding_not_set');
            return false;
        } else {
            switch ($this->settings['encoding']) {
                case 'utf8':
                    $options['encoding'] = 'UTF-8';
                    break;
                case 'iso88591':
                    $options['encoding'] = 'ISO-8859-1';
                    break;
                default:
                    $this->lastErrorCode = self::TEST_ENCODING_NOT_SET;
                    $this->lastErrorMessage = $this->translate('driver.soap.test.encoding_not_recognized');
                    return false;
            }
        }

        $wsdlUrl = $this->getWsdlUrl();
        try {
            $soapClient = new \SoapClient($wsdlUrl, $options);
        } catch (\SoapFault $exception) {
            $this->lastErrorCode = self::TEST_SOAP_ERROR;
            $this->lastErrorMessage = $this->translate(
                'driver.soap.test.soap_error',
                [$exception->getCode(), $exception->getMessage()]
            );
            return false;
        }

        if (isset($soapClient)) {
            unset($soapClient);
        }

        return true;
    }

    /**
     * @return mixed
     */
    protected function getWsdlUrl()
    {

        $url = $this->settings['wsdlFile'];
        if (!empty($this->settings['username'])) {
            $auth = $this->settings['username'];
            if (!empty($this->settings['password'])) {
                $auth .= ':' . $this->settings['password'];
            }
            $auth .= '@';
            if (strpos($url, 'http://') === 0) {
                $url = str_replace('http://', 'http://' . $auth, $url);
            } elseif (strpos($url, 'https://') === 0) {
                $url = str_replace('https://', 'https://' . $auth, $url);
            }
        }
        return $url;
    }

    public function initialize()
    {
        $result = false;
        if (null === $this->soapClient) {
            $wsdlUrl = $this->getWsdlUrl();
            switch ($this->settings['version']) {
                case '1001':
                    $options['soap_version'] = SOAP_1_1;
                    break;
                case '1002':
                    $options['soap_version'] = SOAP_1_2;
                    break;
                default:
                    $this->lastErrorCode = self::TEST_UNRECOGNIZED_VERSION;
                    $this->lastErrorMessage = $this->translate('driver.soap.test.version_not_recognized');
                    return false;
            }

            if (!empty($this->settings['username'])) {
                $options['login'] = $this->settings['username'];
            }

            if (!empty($this->settings['password'])) {
                $options['password'] = $this->settings['password'];
            }
            if (true === (bool)$this->settings['compression']) {
                $options['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
            }
            if (!empty($this->settings['classmap'])) {
                $options['classmap'] = $this->settings['classmap'];
            }

            try {
                $this->soapClient = new \SoapClient($wsdlUrl, $options);
                $result = true;
            } catch (\SoapFault $exception) {
                // TODO: log stuff
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        $this->initialize();
        $signatures = $this->soapClient->__getFunctions();
        $functions = [];
        foreach ($signatures as $signature) {
            if (1 === preg_match(
                    '~(?P<return>[^\s]*) (?P<function>[^(]*)\((?P<arguments>.*)\)~',
                    $signature,
                    $matches
                )
            ) {
                $functions[$matches['function']]['return'] = $matches['return'];
                foreach (GeneralUtility::trimExplode(',', $matches['arguments']) as $key => $argument) {
                    list($argumentType, $argumentName) = explode(' ', $argument);
                    $functions[$matches['function']]['arguments']['[' . $key . '] ' . $argumentName] = $argumentType;
                }
            }
        }
        return $functions;
    }

    /**
     * @param array $classMap
     */
    public function setClassMap(array $classMap)
    {
        if (is_object($this->soapClient)) {
            // destroy current client
            $this->soapClient = null;
        }
        $this->settings['classmap'] = $classMap;
    }

    /**
     * @param string $function
     * @param mixed $parameter
     * @return mixed
     */
    public function call($function, $parameter)
    {
        $this->initialize();
        return call_user_func([$this->soapClient, $function], $parameter);
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return [
            'header' => $this->soapClient->__getLastResponseHeaders(),
            'response' => $this->soapClient->__getLastResponse(),
        ];
    }
}
