<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Connection
 */
class Connection extends AbstractEntity
{
    /**
     * @var string
     */
    protected $identityKey = '';

    /**
     * @var string
     */
    protected $driver = '';

    /**
     * @var array
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
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }
}
