<?php
namespace In2code\In2connector\Controller;

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
     *
     */
    public function indexAction()
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this, __CLASS__ . '@' . __LINE__, 20);die;
    }
}
