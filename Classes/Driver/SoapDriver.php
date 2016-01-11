<?php
namespace In2code\In2connector\Driver;

use In2code\In2connector\Translation\TranslationTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     * @return bool
     */
    public function validateSettings()
    {
        if (empty($this->settings['wsdlFile'])) {
            $this->lastErrorCode = self::TEST_WSDL_FILE_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.soap.test.wsdl_file_empty');
            return false;
        }

        $wsdlUrl = $this->getWsdlUrl();
        $content = $this->getUrl($wsdlUrl);

        if (false === $content) {
            $this->lastErrorCode = self::TEST_WSDL_FILE_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.soap.test.wsdl_file_not_readable');
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
     * @param string $url
     * @return string|false
     */
    protected function getUrl($url)
    {
        $backupCurlTimeout = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout'] = 3;
        $content = GeneralUtility::getUrl($url);
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlTimeout'] = $backupCurlTimeout;
        return $content;
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
}
