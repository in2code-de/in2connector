<?php
namespace In2code\In2connector\Domain\Factory;

use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Class AbstractFactory
 */
abstract class AbstractFactory
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * AbstractFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }
}
