<?php
namespace In2code\In2connector\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class LogEntryRepository
 */
class LogRepository extends Repository
{
    /**
     * Newest log entries first!
     *
     * @var array
     */
    protected $defaultOrderings = [
        'uid' => QueryInterface::ORDER_DESCENDING,
    ];
}