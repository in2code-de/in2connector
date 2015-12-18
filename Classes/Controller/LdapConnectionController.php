<?php
namespace In2code\In2connector\Controller;

use In2code\In2connector\Domain\Model\LdapConnection;

/**
 * Class ConnectionController
 */
class LdapConnectionController extends AbstractConnectionController
{
    /**
     * @var \In2code\In2connector\Domain\Repository\LdapConnectionRepository
     * @inject
     */
    protected $ldapConnectionRepository = null;

    /**
     * @param string $package
     * @param string $identityKey
     * @return void
     */
    public function createAction($package, $identityKey)
    {
        $ldapConnection = new LdapConnection();
        $ldapConnection->setPackage($package);
        $ldapConnection->setIdentityKey($identityKey);
        $this->view->assign('ldapConnection', $ldapConnection);
    }
}
