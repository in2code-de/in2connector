<?php
namespace In2code\In2connector\Persistence\Storage;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 in2code.de and the following authors:
 * Oliver Eglseder <oliver.eglseder@in2code.de>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use In2code\In2connector\Driver\LdapDriver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Comparison;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalAnd;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalNot;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\LogicalOr;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValueInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class LdapQueryParser
 */
class LdapQueryParser
{
    /**
     * @var LdapDriver
     */
    protected $driver = null;

    /**
     * LdapQueryParser constructor.
     */
    public function __construct()
    {
        $this->driver = GeneralUtility::makeInstance(LdapDriver::class);
    }

    /**
     * @param QueryInterface $query
     * @param array $config
     * @return string
     * @throws NotImplementedException
     */
    public function parseQuery(QueryInterface $query, array $config)
    {
        $constraint = $query->getConstraint();
        if ($constraint instanceof ConstraintInterface) {
            return $this->parseConstraint($constraint, array_flip($config['ldap_mapping']['columns']));
        }
        return '(objectClass=*)';
    }

    /**
     * @param ConstraintInterface $constraint
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseConstraint(ConstraintInterface $constraint, array $propertyMap)
    {
        if ($constraint instanceof Comparison) {
            return $this->parseComparison($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalOr) {
            return $this->parseLogicalOr($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalAnd) {
            return $this->parseLogicalAnd($constraint, $propertyMap);
        } elseif ($constraint instanceof LogicalNot) {
            return $this->parseLogicalNot($constraint, $propertyMap);
        }
        return '';
    }

    /**
     * @param PropertyValueInterface $propertyValue
     * @param $propertyMap
     * @return string
     */
    protected function parsePropertyValue(PropertyValueInterface $propertyValue, $propertyMap)
    {
        $propertyName = $propertyValue->getPropertyName();
        if (isset($propertyMap[$propertyName])) {
            return $propertyMap[$propertyName];
        }
        return $propertyName;
    }

    /**
     * @param Comparison $comparison
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseComparison(Comparison $comparison, array $propertyMap)
    {
        $operands = [];
        $operator = $comparison->getOperator();
        $propertyName = $comparison->getOperand1()->getPropertyName();
        $propertyValue = $comparison->getOperand2();

        if (isset($propertyMap[$propertyName])) {
            $propertyName = $propertyMap[$propertyName];
        }
        if ($propertyValue instanceof AbstractEntity) {
            $propertyValue = $propertyValue->getUid();
        }

        $propertyName = ldap_escape($propertyName, null, LDAP_ESCAPE_FILTER);
        if (is_array($propertyValue)) {
            foreach ($propertyValue as $index => $value) {
                $propertyValue[$index] = ldap_escape($value, null, LDAP_ESCAPE_FILTER);
            }
        } else {
            $propertyValue = ldap_escape($propertyValue, null, LDAP_ESCAPE_FILTER);
        }

        if ($operator === QueryInterface::OPERATOR_EQUAL_TO) {
            $operands[] = $propertyName;
            if (empty($propertyValue)) {
                $filter = '(!(%s=*))';
            } else {
                $filter = '(%s=%s)';
                $operands[] = $propertyValue;
            }
        } elseif ($operator === QueryInterface::OPERATOR_EQUAL_TO_NULL) {
            $filter = '(!(%s=*))';
            $operands[] = $propertyName;
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN) {
            $filter = '(!(%s>=%s))';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_LESS_THAN_OR_EQUAL_TO) {
            $filter = '(%s<=%s)';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN) {
            $filter = '(!(%s<=%s))';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_GREATER_THAN_OR_EQUAL_TO) {
            $filter = '(%s>=%s)';
            $operands[] = $propertyName;
            $operands[] = $propertyValue;
        } elseif ($operator === QueryInterface::OPERATOR_LIKE) {
            $operands[] = $propertyName;
            if (empty($propertyValue)) {
                $filter = '(!(%s=*))';
            } else {
                $filter = '(%s=*%s*)';
                $operands[] = $propertyValue;
            }
        } elseif ($operator === QueryInterface::OPERATOR_CONTAINS) {
            $filter = '(|(%s=%s)(%s=*,%s)(%s=%s,*)(%s=*,%s,*))';
            $operands = [
                $propertyName,
                $propertyValue,
            ];
            $operands = array_merge($operands, $operands, $operands, $operands);
        } elseif ($operator === QueryInterface::OPERATOR_IN) {
            $filter = '(|%s)';
            $possibilities = '';
            foreach ($propertyValue as $value) {
                $subComparison = new Comparison($comparison->getOperand1(), QueryInterface::OPERATOR_EQUAL_TO, $value);
                $possibilities .= $this->parseComparison($subComparison, $propertyMap);
            }
            $operands[] = $possibilities;
        } else {
            throw new NotImplementedException('The operator was not implemented (because TYPO3 didn\'t either)');
        }

        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalOr $logicalOr
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalOr(LogicalOr $logicalOr, array $propertyMap)
    {
        $operands = [];
        $filter = '(|%s%s)';
        $operands[] = $this->parseConstraint($logicalOr->getConstraint1(), $propertyMap);
        $operands[] = $this->parseConstraint($logicalOr->getConstraint2(), $propertyMap);
        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalAnd $logicalAnd
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalAnd(LogicalAnd $logicalAnd, array $propertyMap)
    {
        $operands = [];
        $filter = '(&%s%s)';
        $operands[] = $this->parseConstraint($logicalAnd->getConstraint1(), $propertyMap);
        $operands[] = $this->parseConstraint($logicalAnd->getConstraint2(), $propertyMap);
        return vsprintf($filter, $operands);
    }

    /**
     * @param LogicalNot $logicalNot
     * @param array $propertyMap
     * @return string
     * @throws NotImplementedException
     */
    protected function parseLogicalNot(LogicalNot $logicalNot, array $propertyMap)
    {
        $operands = [];
        $filter = '(!%s)';
        $operands[] = $this->parseConstraint($logicalNot->getConstraint(), $propertyMap);
        return vsprintf($filter, $operands);
    }
}
