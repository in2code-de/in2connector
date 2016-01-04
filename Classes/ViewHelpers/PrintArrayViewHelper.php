<?php
namespace In2code\In2connector\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class PrintArrayViewHelper
 */
class PrintArrayViewHelper extends AbstractViewHelper
{
    /**
     * @param array $array
     * @return string
     */
    public function render($array)
    {
        if (count($array) > 0) {
            return '<pre>' . print_r($array, true) . '</pre>';
        }
        return '';
    }
}
