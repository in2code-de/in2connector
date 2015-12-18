<?php
namespace In2code\In2connector\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ConnectionController
 */
class ConnectionController extends ActionController
{
    /**
     * @var \In2code\In2connector\Domain\Factory\ConnectionFactory
     * @inject
     */
    protected $connectionFactory = null;

    /**
     * @var \In2code\In2connector\Domain\Factory\RepositoryFactory
     * @inject
     */
    protected $repositoryFactory = null;


    /**
     * @param string $package
     * @param string $identityKey
     * @param string $className
     */
    public function addAction($package, $identityKey, $className)
    {
        $connection = $this->connectionFactory->createConnectionByClassName($className, $package, $identityKey);
        $repository = $this->repositoryFactory->getRepositoryForClassName($className);
        $repository->add($connection);
        $this->redirect('index', 'Dashboard');
    }
}
