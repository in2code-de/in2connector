<?php
namespace In2code\In2connector\Driver;

use In2code\In2connector\Driver\Exception\ErrorException;
use In2code\In2connector\Logging\LoggerTrait;
use In2code\In2connector\Service\ConfigurationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractDriver
 */
abstract class AbstractDriver
{
    use LoggerTrait;

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
     * AbstractDriver constructor.
     */
    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
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
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
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
                $this->getLogger()->error(
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
     */
    protected function investigateError($errorCode, $errorMessage, $file, $line, $context)
    {
        return false;
    }
}
