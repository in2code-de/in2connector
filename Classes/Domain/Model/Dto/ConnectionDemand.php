<?php
namespace In2code\In2connector\Domain\Model\Dto;

/**
 * Class ConnectionDemand
 */
class ConnectionDemand
{
    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var string
     */
    protected $driverName = '';

    /**
     * ConnectionDemand constructor.
     *
     * @param string $identityKey
     * @param string $driverName
     */
    public function __construct($identityKey, $driverName)
    {
        $this->identityKey = $identityKey;
        $this->driverName = $driverName;
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
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @param string $driverName
     */
    public function setDriverName($driverName)
    {
        $this->driverName = $driverName;
    }
}
