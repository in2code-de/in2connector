<?php
namespace In2code\In2connector\Domain\Factory;

use In2code\In2connector\Domain\Repository\AbstractConnectionRepository;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;

/**
 * Class RepositoryFactory
 */
class RepositoryFactory extends AbstractFactory
{
    /**
     * @param string $className
     * @return AbstractConnectionRepository
     */
    public function getRepositoryForClassName($className)
    {
        return $this->objectManager->get(ClassNamingUtility::translateModelNameToRepositoryName($className));
    }
}
