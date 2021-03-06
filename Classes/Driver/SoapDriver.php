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

        $originalTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 3);

        $options['connection_timeout'] = 3;

        $wsdlUrl = $this->getWsdlUrl();
        try {
            $soapClient = new \SoapClient($wsdlUrl, $options);
        } catch (\SoapFault $exception) {
            $this->lastErrorCode = self::TEST_SOAP_ERROR;
            $this->lastErrorMessage = $this->translate(
                'driver.soap.test.soap_error',
                [$exception->getCode(), $exception->getMessage()]
            );
            ini_set('default_socket_timeout', $originalTimeout);

            return false;
        }

        ini_set('default_socket_timeout', $originalTimeout);
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
        $originalTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $this->settings['timeout']);

        $options = [];
        $options['connection_timeout'] = $this->settings['timeout'];

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
        ini_set('default_socket_timeout', $originalTimeout);
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
    public function call($function, $parameter = null)
    {
        $this->initialize();
        try {
            if (null === $parameter) {
                $result = call_user_func([$this->soapClient, $function]);
            } else {
                $result = call_user_func([$this->soapClient, $function], $parameter);
            }
        } catch (\SoapFault $exception) {
            $this->logger->error(
                'SOAP call failed',
                ['code' => $exception->getCode(), 'message' => $exception->getMessage()]
            );
            $result = false;
        }
        if (false === $result) {
            $this->fetchErrors();
        }
        return $result;
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

    /**
     * @return false
     */
    public function fetchErrors()
    {
        $this->lastErrorMessage = $this->soapClient->__getLastResponse();
        $this->lastErrorCode = $this->soapClient->__getLastResponseHeaders();
        $this->logger->error(
            'Fetched errors from soap client',
            [
                'code' => $this->lastErrorCode,
                'message' => $this->lastErrorMessage,
            ]
        );
        return false;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return sprintf('[Code %s] %s', $this->lastErrorCode, $this->lastErrorMessage);
    }

    /**
     * Always returns true
     *
     * @return bool
     */
    public function resetErrors()
    {
        $this->lastErrorCode = 0;
        $this->lastErrorMessage = '';
        return true;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return 0 !== $this->lastErrorCode || '' !== $this->lastErrorMessage;
    }
}
