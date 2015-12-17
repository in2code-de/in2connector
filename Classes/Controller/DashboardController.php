<?php
namespace In2code\In2connector\Controller;

use In2code\In2connector\Domain\Model\Cato\ConnectionCato;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DashboardController
 */
class DashboardController extends ActionController
{
    /**
     * @var \In2code\In2connector\Registry\ConnectionRegistry
     * @inject
     */
    protected $connectionRegistry = null;

    /**
     * @var \In2code\In2connector\Domain\Repository\SoapConnectionRepository
     * @inject
     */
    protected $soapConnectionRepository = null;

    /**
     * @var \In2code\In2connector\Domain\Repository\LdapConnectionRepository
     * @inject
     */
    protected $ldapConnectionRepository = null;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->view->assign(
            'connectionDco',
            new ConnectionCato(
                $this->connectionRegistry->getRequiredConnections(), [
                    $this->soapConnectionRepository->findAll(),
                    $this->ldapConnectionRepository->findAll(),
                ]
            )
        );
    }
}
