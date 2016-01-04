<?php
namespace In2code\In2connector\Registry;

use In2code\In2connector\Domain\Model\Dto\ConnectionDemand;
use In2code\In2connector\Domain\Model\Dto\DriverRegistration;
use In2code\In2connector\Driver\AbstractDriver;
use In2code\In2connector\Logging\LoggerTrait;
use In2code\In2connector\Registry\Exceptions\ConnectionAlreadyDemandedException;
use In2code\In2connector\Registry\Exceptions\DriverDoesNotExistException;
use In2code\In2connector\Registry\Exceptions\DriverNameAlreadyRegisteredException;
use In2code\In2connector\Registry\Exceptions\DriverNameNotRegistered;
use In2code\In2connector\Registry\Exceptions\InvalidDriverException;
use In2code\In2connector\Service\ConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConnectionRegistry
 */
class ConnectionRegistry implements SingletonInterface
{
    use LoggerTrait;

    /**
     * @var \In2code\In2connector\Service\ConfigurationService
     */
    protected $configurationService = null;

    /**
     * @var DriverRegistration[]
     */
    protected $registeredDrivers = [];

    /**
     * @var ConnectionDemand[]
     */
    protected $demandedConnections = [];

    /**
     * ConnectionRegistry constructor.
     */
    public function __construct()
    {
        $this->configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * @param string $name
     * @param string $class
     * @return bool
     * @throws DriverNameAlreadyRegisteredException
     * @throws InvalidDriverException
     */
    public function registerDriver($name, $class)
    {
        $this->getLogger()->debug(
            'Registering driver',
            ['function' => __FUNCTION__, 'name' => $name, 'class' => $class]
        );

        if (isset($this->registeredDrivers[$name])) {
            $message = 'The driver name "' . $name . '" was already registered';
            if ($this->configurationService->isProductionContext()) {
                $this->getLogger()->critical(
                    $message,
                    ['function' => __FUNCTION__, 'name' => $name, 'class' => $class]
                );
                return false;
            } else {
                throw new DriverNameAlreadyRegisteredException($message, 1451926330);
            }
        }

        if (!is_subclass_of($class, AbstractDriver::class)) {
            $message = 'The driver class "' . $class
                       . '" is not a valid driver. It must inherit from \In2code\In2connector\Driver\AbstractDriver';
            if ($this->configurationService->isProductionContext()) {
                $this->getLogger()->emergency(
                    $message,
                    ['function' => __FUNCTION__, 'name' => $name, 'class' => $class]
                );
                return false;
            } else {
                throw new InvalidDriverException($message, 1451926497);
            }
        }

        $this->registeredDrivers[$name] = new DriverRegistration($name, $class);
        return true;
    }

    /**
     * @param string $name
     * @return bool
     * @throws DriverNameNotRegistered
     */
    public function deregisterDriver($name)
    {
        $this->getLogger()->info('Deregistering driver', ['function' => __FUNCTION__, 'name' => $name]);

        if (!isset($this->registeredDrivers[$name])) {
            $message = 'The driver name "' . $name . '" was never registered';
            if ($this->configurationService->isProductionContext()) {
                $this->getLogger()->error($message, ['function' => __FUNCTION__, 'name' => $name]);
                return false;
            } else {
                throw new DriverNameNotRegistered($message, 1451927180);
            }
        }

        unset($this->registeredDrivers[$name]);
        return true;
    }

    /**
     * @param string $identityKey
     * @param string $driverName
     * @return bool
     * @throws ConnectionAlreadyDemandedException
     * @throws DriverDoesNotExistException
     */
    public function demandConnection($identityKey, $driverName)
    {
        $this->getLogger()->info(
            'Connection demanded',
            ['function' => __FUNCTION__, 'identityKey' => $identityKey, 'driverName' => $driverName]
        );

        if (isset($this->demandedConnections[$identityKey])) {
            $message = 'The connection with the identity key "' . $identityKey . '" was already demanded';
            if ($this->configurationService->isProductionContext()) {
                $this->getLogger()->error(
                    $message,
                    ['function' => __FUNCTION__, 'identityKey' => $identityKey, 'driverName' => $driverName]
                );
                return false;
            } else {
                throw new ConnectionAlreadyDemandedException($message, 1451927594);
            }
        }

        if (!isset($this->registeredDrivers[$driverName])) {
            $message = 'The requested driver for the identity key "' . $identityKey . '" was not registered';
            if ($this->configurationService->isProductionContext()) {
                $this->getLogger()->alert(
                    $message,
                    ['function' => __FUNCTION__, 'identityKey' => $identityKey, 'driverName' => $driverName]
                );
                return false;
            } else {
                throw new DriverDoesNotExistException($message, 1451927594);
            }
        }

        $this->demandedConnections[$identityKey] = new ConnectionDemand($identityKey, $driverName);
        return true;
    }

    /**
     * @return DriverRegistration[]
     */
    public function getRegisteredDrivers()
    {
        return $this->registeredDrivers;
    }

    /**
     * @return ConnectionDemand[]
     */
    public function getDemandedConnections()
    {
        return $this->demandedConnections;
    }
}
