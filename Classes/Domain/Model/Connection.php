<?php
namespace In2code\In2connector\Domain\Model;

use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class Connection
 */
class Connection extends AbstractEntity
{
    const TEST_RESULT_OK = 0;
    const TEST_RESULT_INFO = 1;
    const TEST_RESULT_WARNING = 2;
    const TEST_RESULT_ERROR = 3;

    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var string
     */
    protected $driver = '';

    /**
     * @var string
     */
    protected $settings = [];

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
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return string
     * @throws \In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException
     */
    public function getSettingsPartial()
    {
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        return $connectionRegistry->getRegisteredDriver($this->driver)->getSettingsPartial();
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        if (is_array($this->settings)) {
            return $this->settings;
        } else {
            return json_decode($this->settings, true);
        }
    }

    /**
     * @param array|string $settings
     */
    public function setSettings($settings)
    {
        if (is_string($settings)) {
            $this->settings = $settings;
        } elseif (is_array($settings)) {
            $this->settings = json_encode($settings);
        }
    }

    /*********************************************
     *
     *                 TESTING
     *
     *********************************************/

    /**
     * @var null|int
     */
    protected $testResult = null;

    /**
     * @var string
     */
    protected $testResultMessage = '';

    /**
     *
     */
    public function getConnectionTestResult()
    {
        if (null === $this->testResult) {
            $this->testResultMessage = '';
            if ($this->driver === '') {
                $this->testResultMessage = LocalizationUtility::translate(
                    'domain.model.connection.connection_test.result.no_driver',
                    'in2connector'
                );
                return self::TEST_RESULT_ERROR;
            } else {
                $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
                try {
                    $driver = $connectionRegistry->getRegisteredDriver($this->driver);
                } catch (DriverNameNotRegisteredException $e) {
                    $driver = false;
                }
                if (!$driver) {
                    $this->testResultMessage = LocalizationUtility::translate(
                        'domain.model.connection.connection_test.result.driver_not_registered',
                        'in2connector'
                    );
                    return self::TEST_RESULT_ERROR;
                }
            }
        }
        return self::TEST_RESULT_OK;
    }

    /**
     *
     */
    public function resetTestResult()
    {
        $this->testResult = null;
    }
}
