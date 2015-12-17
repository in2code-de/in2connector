<?php
namespace In2code\In2connector\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractConnection
 */
abstract class AbstractConnection extends AbstractEntity
{
    /**
     * @var string
     */
    protected $package = '';

    /**
     * @var string
     */
    protected $identityKey = '';

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
}
