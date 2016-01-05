<?php
namespace In2code\In2connector\Translation;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TranslationTrait
 */
trait TranslationTrait
{
    /**
     * @param string $key
     * @param array|null $arguments
     * @return NULL|string
     */
    protected function translate($key, array $arguments = null)
    {
        return LocalizationUtility::translate($key, 'in2connector', $arguments);
    }
}
