<?php
namespace In2code\In2connector\Domain\Model;

use In2code\In2connector\Registry\ConnectionRegistry;
use In2code\In2connector\Registry\Exceptions\DriverNameNotRegisteredException;
use In2code\In2connector\Translation\TranslationTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Connection
 */
class Connection extends AbstractEntity
{
    use TranslationTrait;
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
        $partial = 'Driver/Blank';
        $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
        $driverRegistration = $connectionRegistry->getRegisteredDriver($this->driver);
        if (false !== $driverRegistration) {
            $partial = $driverRegistration->getSettingsPartial();
        }
        return $partial;
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
     * @var bool
     */
    protected $testExecuted = false;

    /**
     * @var string
     */
    protected $testResultMessage = '';

    /**
     * @var string
     */
    protected $testResultCode = 0;

    /**
     * @return string
     */
    public function getTestResultMessage()
    {
        return $this->testResultMessage;
    }

    /**
     * @return int
     */
    public function getConnectionTestResult()
    {
        if (false === $this->testExecuted) {
            $this->testResultCode = self::TEST_RESULT_OK;
            if ($this->driver === '') {
                $this->testResultMessage = $this->translate(
                    'domain.model.connection.connection_test.result.no_driver'
                );
                $this->testResultCode = self::TEST_RESULT_ERROR;
            } else {
                $connectionRegistry = GeneralUtility::makeInstance(ConnectionRegistry::class);
                try {
                    $driverRegistration = $connectionRegistry->getRegisteredDriver($this->driver);
                } catch (DriverNameNotRegisteredException $e) {
                    $driverRegistration = false;
                }
                if (!$driverRegistration) {
                    $this->testResultMessage = $this->translate(
                        'domain.model.connection.connection_test.result.driver_not_registered'
                    );
                    $this->testResultCode = self::TEST_RESULT_ERROR;
                } else {
                    $driverInstance = $driverRegistration->getDriverInstance();
                    $driverInstance->setSettings($this->getSettings());
                    if (!$driverInstance->validateSettings()) {
                        $this->testResultMessage = $driverInstance->getLastErrorMessage();
                        $this->testResultCode = self::TEST_RESULT_WARNING;
                    }
                }
            }
        }
        return $this->testResultCode;
    }

    /**
     *
     */
    public function resetTestResult()
    {
        $this->testExecuted = false;
        $this->testResultMessage = '';
    }
}
