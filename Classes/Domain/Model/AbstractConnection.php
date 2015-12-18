<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractConnection
 */
abstract class AbstractConnection extends AbstractEntity
{
    const COMBINED_IDENTITY_GLUE = '|';
    const STATUS_UNDEFINED = -1;
    const STATUS_REQUIREMENT_MATCH_EXISTING = 0;
    const STATUS_REQUIRED = 1;
    const STATUS_ORPHANED = 2;

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
    protected $status = self::STATUS_UNDEFINED;

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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
}
