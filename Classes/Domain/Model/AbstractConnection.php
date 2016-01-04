<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractConnection
 */
abstract class AbstractConnection extends AbstractEntity
{
    const COMBINED_IDENTITY_GLUE = '|';
    const REQUIRED_STATUS_UNDEFINED = -1;
    const REQUIRED_STATUS_MATCH = 0;
    const REQUIRED_STATUS_REQUIRED = 1;
    const REQUIRED_STATUS_ORPHANED = 2;
    const CONNECTION_STATUS_UNDEFINED = -1;
    const CONNECTION_STATUS_OK = 0;
    const CONNECTION_STATUS_WARNING = 1;
    const CONNECTION_STATUS_ERROR = 2;

    /**
     * @var string
     */
    protected $package = '';

    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @transient
     * @var int
     */
    protected $requiredStatus = self::REQUIRED_STATUS_UNDEFINED;

    /**
     * @transient
     * @var int
     */
    protected $connectionStatus = self::CONNECTION_STATUS_UNDEFINED;

    /**
     * Error message
     *
     * @transient
     * @var string
     */
    protected $connectionError = '';

    /**
     * @return string
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param string $package
     */
    public function setPackage($package)
    {
        $this->package = $package;
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
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getRequiredStatus()
    {
        return $this->requiredStatus;
    }

    /**
     * @param int $requiredStatus
     */
    public function setRequiredStatus($requiredStatus)
    {
        $this->requiredStatus = $requiredStatus;
    }

    /**
     * @return string
     */
    public function getCombinedIdentity()
    {
        return self::combineIdentity($this->package, $this->identityKey);
    }

    /**
     * @param string $package
     * @param string $identityKey
     * @return string
     */
    public static function combineIdentity($package, $identityKey)
    {
        return $package . self::COMBINED_IDENTITY_GLUE . $identityKey;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return get_class($this);
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        $connectionClassName = $this->getClassName();
        return substr($connectionClassName, strrpos($connectionClassName, '\\Domain\\Model\\') + 14);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return get_class($this);
    }

    /**
     * @return int
     */
    public function getConnectionStatus()
    {
        return $this->testConnect();
    }

    /**
     * @return mixed
     */
    public function getConnectionError()
    {
        return $this->connectionError;
    }

    /**
     * Same as $this->connect
     *
     * @return mixed
     */
    protected function testConnect()
    {
        $this->connect();
        $status = $this->connectionStatus;
        $this->disconnect();
        return $status;
    }

    /**
     * Must not throw an exception but set this->connectionStatus
     *
     * @return mixed
     */
    abstract protected function connect();

    /**
     * Must not throw an exception and this->connectionStatus to self::CONNECTION_STATUS_UNDEFINED
     *
     * @return mixed
     */
    abstract protected function disconnect();
}
