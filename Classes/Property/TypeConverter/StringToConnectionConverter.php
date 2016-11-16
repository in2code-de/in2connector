<?php
namespace In2code\In2connector\Property\TypeConverter;

/*
 * Copyright notice
 *
 * (c) 2015-2016 Oliver Eglseder <oliver.eglseder@in2code.de>, in2code GmbH
 *
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use In2code\In2connector\Domain\Model\Connection;
use In2code\In2connector\Domain\Repository\ConnectionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class StringToConnectionConverter extends AbstractTypeConverter
{
    /**
     * The source types this converter can convert.
     *
     * @var array<string>
     * @api
     */
    protected $sourceTypes = ['string', 'array'];

    /**
     * @var string
     */
    protected $targetType = 'object';

    /**
     * This implementation always returns TRUE for this method.
     *
     * @param mixed $source the source data
     * @param string $targetType the type to convert to.
     * @return bool TRUE if this TypeConverter can convert from $source to $targetType, FALSE otherwise.
     * @api
     */
    public function canConvertFrom($source, $targetType)
    {
        return (is_string($source) || is_array($source)) && $targetType === Connection::class;
    }

    /**
     * @param string $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        return GeneralUtility::makeInstance(ConnectionRepository::class)->findOneByUid($source);
    }
}
