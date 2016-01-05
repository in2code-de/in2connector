<?php
namespace In2code\In2connector\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AvailableDriversViewHelper
 */
class AvailableDriversViewHelper extends AbstractViewHelper
{
    /**
     * @var \In2code\In2connector\Registry\ConnectionRegistry
     * @inject
     */
    protected $connectionRegistry = null;

    /**
     * @return \In2code\In2connector\Domain\Model\Dto\DriverRegistration[]
     */
    public function render()
    {
        return $this->connectionRegistry->getRegisteredDrivers();
    }
}
