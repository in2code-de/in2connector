<?php
namespace In2code\In2connector\Driver;

/***************************************************************
 * Copyright notice
 *
 * (c) 2015 in2code.de and the following authors:
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

use In2code\In2connector\Translation\TranslationTrait;

/**
 * Class LdapDriver
 */
class LdapDriver extends AbstractDriver
{
    use TranslationTrait;
    const LDAPS_PROTOCOL = 'ldaps://';
    const TEST_OK = 0;
    const TEST_HOSTNAME_EMPTY = 100;
    const TEST_WRONG_PORT = 101;
    const TEST_PROTOCOL_VERSION_ERROR = 102;
    const TEST_CONNECTION_FAILED = 103;
    const TEST_COULD_NOT_SET_PROTOCOL_VERSION = 104;
    const TEST_WRONG_CREDENTIALS = 105;
    const TEST_PROTOCOL_VERSION_MISMATCH = 106;
    const TEST_BASE_DN_NOT_READABLE = 107;
    const TEST_SERVER_UNREACHABLE = 201;
    const ERROR_SEARCH_FAILED = 201;

    /**
     * @var resource
     */
    protected $connection = null;

    /**
     * @var bool
     */
    protected $testResult = true;

    /**
     * @var null|string
     */
    protected $lastQuery = null;

    /**
     * @return bool
     */
    public function validateSettings()
    {
        $this->captureErrors(true);

        $this->lastErrorCode = self::TEST_OK;
        $this->lastErrorMessage = $this->translate('driver.ldap.test.ok');

        if (empty($this->settings['hostname'])) {
            $this->lastErrorCode = self::TEST_HOSTNAME_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.ldap.test.hostname_empty');
            $this->captureErrors(false);
            return false;
        }

        if (isset($this->settings['port'])) {
            $port = (int)$this->settings['port'];
            if ($port <= 0) {
                $this->lastErrorCode = self::TEST_WRONG_PORT;
                $this->lastErrorMessage = $this->translate('driver.ldap.test.wrong_port', [$port]);
                $this->captureErrors(false);
                return false;
            }
        }

        if (strpos($this->settings['hostname'], 'ldaps:') === 0) {
            $host = $this->settings['hostname'] . ':' . $this->settings['port'];
            $connection = ldap_connect($host);
        } elseif (true === (bool)$this->settings['ldaps']) {
            $host = rtrim(self::LDAPS_PROTOCOL . $this->settings['hostname'], '/') . ':' . $this->settings['port'];
            $connection = ldap_connect($host);
        } else {
            $connection = ldap_connect($this->settings['hostname'], $this->settings['port']);
        }
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

        // reduce timeout to prevent php timeout and waiting time in the connection overview
        ldap_set_option($connection, LDAP_OPT_NETWORK_TIMEOUT, 3);

        // check investigated error
        if ($this->lastErrorCode !== self::TEST_OK) {
            $this->captureErrors(false);
            return false;
        } elseif (!is_resource($connection)) {
            $this->lastErrorCode = self::TEST_CONNECTION_FAILED;
            $this->lastErrorMessage = $this->translate('driver.ldap.test.connection_failed');
            $this->captureErrors(false);
            return false;
        }

        if ($this->settings['protocolVersion'] !== 'NULL') {
            if (!in_array((int)$this->settings['protocolVersion'], [2, 3])) {
                $this->lastErrorCode = self::TEST_PROTOCOL_VERSION_ERROR;
                $this->lastErrorMessage = $this->translate('driver.ldap.test.protocol_version_error');
                $this->captureErrors(false);
                return false;
            } else {
                if (!ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, (int)$this->settings['protocolVersion'])) {
                    $this->lastErrorCode = self::TEST_COULD_NOT_SET_PROTOCOL_VERSION;
                    $this->lastErrorMessage = $this->translate('driver.ldap.test.protocol_version_not_settable');
                    $this->captureErrors(false);
                    return false;
                }
            }
        }

        if (!ldap_bind($connection, $this->settings['username'], $this->settings['password'])) {
            // check investigated error
            if ($this->lastErrorCode !== self::TEST_OK) {
                $this->captureErrors(false);
                return false;
            }
            $this->lastErrorCode = self::TEST_WRONG_CREDENTIALS;
            $this->lastErrorMessage = $this->translate('driver.ldap.test.wrong_credentials');
            $this->captureErrors(false);
            return false;
        }

        if (!empty($this->settings['baseDn'])) {
            $result = ldap_list($connection, $this->settings['baseDn'], 'ou=*');
            // check investigated error but overwrite with correct information since there are no RDNs
            if ($this->lastErrorCode !== self::TEST_OK && !is_resource($result)) {
                $this->lastErrorCode = self::TEST_BASE_DN_NOT_READABLE;
                $this->lastErrorMessage = $this->translate('driver.ldap.test.base_dn_not_readable');
                $this->captureErrors(false);
                return false;
            }
        }

        ldap_unbind($connection);

        $this->captureErrors(false);
        return true;
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     * @param string $file
     * @param int $line
     * @param array $context
     * @return bool
     */
    protected function investigateError($errorCode, $errorMessage, $file, $line, $context)
    {
        if (0 === strpos($errorMessage, 'ldap_connect(): invalid port number')) {
            if (1 === preg_match('~port number: (\d)+~', $errorMessage, $matches)) {
                $port = $matches[1];
                $this->lastErrorCode = self::TEST_WRONG_PORT;
                $this->lastErrorMessage = $this->translate('driver.ldap.test.wrong_port', [$port]);
                $this->logger->error(
                    'The port number "' . $port . '" is invalid.',
                    ['errorCode' => $errorCode, 'errorMessage' => $errorMessage, 'file' => $file, 'line' => $line]
                );
                return true;
            }
        } elseif ('ldap_bind(): Unable to bind to server: Protocol error' === $errorMessage) {
            $this->lastErrorCode = self::TEST_PROTOCOL_VERSION_MISMATCH;
            $this->lastErrorMessage = $this->translate(
                'driver.ldap.test.protocol_version_mismatch',
                [$this->settings['protocolVersion']]
            );
            $this->logger->error(
                'The protocol version ' . $this->settings['protocolVersion'] . ' was not accepted',
                ['errorCode' => $errorCode, 'errorMessage' => $errorMessage, 'file' => $file, 'line' => $line]
            );
            return true;
        } elseif ($errorMessage === 'ldap_list(): Search: No such object') {
            $this->lastErrorCode = self::ERROR_SEARCH_FAILED;
            $this->lastErrorMessage = $this->translate('driver.ldap.search_failed');
            $this->logger->error(
                'The configured search could not be executed. Check your base DN and RDNs',
                [
                    'baseDN' => $this->settings['baseDn'],
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'file' => $file,
                    'line' => $line,
                ]
            );
            return true;
        } elseif ('ldap_bind(): Unable to bind to server: Can\'t contact LDAP server' === $errorMessage) {
            $this->lastErrorCode = self::TEST_SERVER_UNREACHABLE;
            $this->lastErrorMessage = $this->translate('driver.ldap.server_unreachable');
            $this->logger->error(
                'Could not connect to the server. Check the hostname and port and server status',
                [
                    'hostname' => $this->settings['hostname'],
                    'port' => $this->settings['port'],
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'file' => $file,
                    'line' => $line,
                ]
            );
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function initialize()
    {
        if (is_resource($this->connection)) {
            return false;
        }

        if (strpos($this->settings['hostname'], 'ldaps:') === 0) {
            $host = $this->settings['hostname'] . ':' . $this->settings['port'];
            $this->connection = ldap_connect($host);
        } elseif (true === (bool)$this->settings['ldaps']) {
            $host = rtrim(self::LDAPS_PROTOCOL . $this->settings['hostname'], '/') . ':' . $this->settings['port'];
            $this->connection = ldap_connect($host);
        } else {
            $this->connection = ldap_connect($this->settings['hostname'], $this->settings['port']);
        }
        if (false === $this->connection) {
            $this->logger->error(
                sprintf(
                    'Connection to "%s" on port [%d] failed',
                    $this->settings['hostname'],
                    $this->settings['port']
                )
            );
        }
        ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, $this->settings['timeout']);
        if ('NULL' !== $this->settings['protocolVersion']) {
            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, (int)$this->settings['protocolVersion']);
        }
        $success = ldap_bind($this->connection, $this->settings['username'], $this->settings['password']);
        if (false === $success) {
            $this->logger->error(
                sprintf('Authentication to LDAP failed for user "%s"', $this->settings['username'])
            );
        }
        return $success;
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @param int|null $limit
     * @return false|resource
     */
    public function listDirectory($distinguishedName = '', $filter = '', $attributes = [], $limit = null)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'listDirectory',
            'dn' => $distinguishedName,
            'filter' => $filter,
            'attr' => $attributes,
            'limit' => $limit,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_list($this->connection, $distinguishedName, $filter, $attributes, 0, $limit);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param resource $resource
     * @return array|bool
     */
    public function getResults($resource)
    {
        $this->initialize();
        $return = ldap_get_entries($this->connection, $resource);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param resource $resource
     * @return int|bool
     */
    public function countResults($resource)
    {
        $this->initialize();
        $return = ldap_count_entries($this->connection, $resource);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param resource $resource
     * @return bool
     */
    public function freeResult($resource)
    {
        $return = ldap_free_result($resource);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @param int|null $limit
     * @return false|resource
     */
    public function search($distinguishedName = '', $filter = '', array $attributes = [], $limit = null)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'search',
            'dn' => $distinguishedName,
            'filter' => $filter,
            'attr' => $attributes,
            'limit' => $limit,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_search($this->connection, $distinguishedName, $filter, $attributes, 0, $limit);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param resource $resource
     * @return false|resource
     */
    public function fetchFirst($resource)
    {
        $this->initialize();
        $entry = ldap_first_entry($this->connection, $resource);
        return ($entry === false ? $this->fetchErrors() : $entry);
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @param int|null $limit
     * @return array|bool
     */
    public function searchAndGetResults($distinguishedName = '', $filter = '', array $attributes = [], $limit = null)
    {
        $return = $this->search($distinguishedName, $filter, $attributes, $limit);
        return ($return === false ?: $this->getResults($return));
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @param int|null $limit
     * @return bool|int
     */
    public function searchAndCountResults($distinguishedName = '', $filter = '', array $attributes = [], $limit = null)
    {
        $return = $this->search($distinguishedName, $filter, $attributes, $limit);
        return ($return === false ?: $this->countResults($return));
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @param int|null $limit
     * @return array|bool
     */
    public function listAndGetResults($distinguishedName = '', $filter = '', $attributes = [], $limit = null)
    {
        $return = $this->listDirectory($distinguishedName, $filter, $attributes, $limit);
        return ($return === false ?: $this->getResults($return));
    }

    /**
     * @param resource $entry
     * @return string
     */
    public function getDnOfEntry($entry)
    {
        $this->initialize();
        $return = ldap_get_dn($this->connection, $entry);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param $distinguishedName
     * @return bool
     */
    public function delete($distinguishedName)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'delete',
            'dn' => $distinguishedName,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_delete($this->connection, $distinguishedName);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param string $distinguishedName
     * @param array $values
     * @return bool
     */
    public function modify($distinguishedName, array $values)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'delete',
            'dn' => $distinguishedName,
            'values' => $values,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_modify($this->connection, $distinguishedName, $values);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param $distinguishedName
     * @param array $values
     * @return bool
     */
    public function add($distinguishedName, array $values)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'add',
            'dn' => $distinguishedName,
            'values' => $values,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_add($this->connection, $distinguishedName, $values);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @param string $distinguishedName
     * @param array $values
     * @return bool
     */
    public function removeAttributes($distinguishedName, array $values)
    {
        $this->initialize();
        $this->lastQuery = [
            'action' => 'removeAttributes',
            'dn' => $distinguishedName,
            'values' => $values,
        ];
        $distinguishedName = $this->expandRdn($distinguishedName);
        $return = ldap_mod_del($this->connection, $distinguishedName, $values);
        return ($return === false ? $this->fetchErrors() : $return);
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        $this->initialize();
        $info = [];
        ldap_get_option($this->connection, LDAP_OPT_DEREF, $info['LDAP_OPT_DEREF']);
        ldap_get_option($this->connection, LDAP_OPT_SIZELIMIT, $info['LDAP_OPT_SIZELIMIT']);
        ldap_get_option($this->connection, LDAP_OPT_TIMELIMIT, $info['LDAP_OPT_TIMELIMIT']);
        ldap_get_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, $info['LDAP_OPT_NETWORK_TIMEOUT']);
        ldap_get_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $info['LDAP_OPT_PROTOCOL_VERSION']);
        ldap_get_option($this->connection, LDAP_OPT_ERROR_NUMBER, $info['LDAP_OPT_ERROR_NUMBER']);
        ldap_get_option($this->connection, LDAP_OPT_REFERRALS, $info['LDAP_OPT_REFERRALS']);
        ldap_get_option($this->connection, LDAP_OPT_RESTART, $info['LDAP_OPT_RESTART']);
        ldap_get_option($this->connection, LDAP_OPT_HOST_NAME, $info['LDAP_OPT_HOST_NAME']);
        ldap_get_option($this->connection, LDAP_OPT_ERROR_STRING, $info['LDAP_OPT_ERROR_STRING']);
        ldap_get_option($this->connection, LDAP_OPT_MATCHED_DN, $info['LDAP_OPT_MATCHED_DN']);
        ldap_get_option($this->connection, LDAP_OPT_SERVER_CONTROLS, $info['LDAP_OPT_SERVER_CONTROLS']);
        ldap_get_option($this->connection, LDAP_OPT_CLIENT_CONTROLS, $info['LDAP_OPT_CLIENT_CONTROLS']);
        return $info;
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @param array $attributes
     * @return array|bool|false
     */
    public function getAttributes($distinguishedName, $filter = '', $attributes = [])
    {
        $result = $this->search($distinguishedName, $filter, $attributes);
        if (false === $result) {
            return false;
        }
        $this->lastQuery = [
            'action' => 'getAttributes',
            'dn' => $distinguishedName,
            'filter' => $filter,
            'attributes' => $attributes,
        ];
        $attributes = ldap_get_attributes($this->connection, $result);
        if ($attributes === false) {
            return $this->fetchErrors();
        }
        return $this->getResults($attributes);
    }

    /**
     *
     */
    public function logout()
    {
        if (is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }
        unset($this->connection);
    }

    /**
     * @param string $distinguishedName
     * @param string $password
     * @return bool
     */
    public function testLogin($distinguishedName, $password)
    {
        $settings = $this->settings;
        $distinguishedName = $this->expandRdn($distinguishedName);

        $settings['username'] = $distinguishedName;
        $settings['password'] = $password;

        $ldapDriver = clone $this;
        $ldapDriver->setSettings($settings);
        $ldapDriver->logout();

        $this->testResult = true;
        set_error_handler([$this, 'testFail']);
        try {
            $result = $ldapDriver->initialize();
        } catch (\Exception $exception) {
            $result = false;
        }
        restore_error_handler();
        $ldapDriver->logout();

        if (false === $this->testResult) {
            $this->testResult = true;
            return false;
        }
        return $result;
    }

    /**
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testFail($level, $message, $file, $line, array $context)
    {
        if (strpos($message, 'Invalid credentials') !== false) {
            $this->testResult = false;
        }
    }

    /**
     * Escapes LDAP Characters and Prevents LDAP Injection
     *
     * TODO: Rewrite this to match https://tools.ietf.org/html/rfc4514
     *
     * Example:
     * $user = '*)(username=test+1234@lightwerk.com)';
     * var_dump("cn=" . Ldap::escape($user));
     * // string(64) "cn=\5c2a\5c29\5c28username\3dtest\2b1234@lightwerk.com\5c29"
     * var_dump("cn=" . Ldap::escape($user, true));
     * // string(52) "cn=\2a\29\28username=test+1234@lightwerk.com\29"
     * var_dump("cn=" . Ldap::escape($user, false));
     * // string(48) "cn=*)(username\3dtest\>2b1234@lightwerk.com)"
     *
     * @param String $string
     * @param Boolean $distinguishedName
     * @return String
     */
    public function escape($string, $distinguishedName = null)
    {
        $escapeDn = array('\\', '*', '(', ')', "\x00");
        $escape = array('\\', ',', '=', '+', '<', '>', ';', '"', '#');

        $search = array();
        if ($distinguishedName === null) {
            $search = array_merge($search, $escapeDn, $escape);
        } elseif ($distinguishedName === false) {
            $search = array_merge($search, $escape);
        } else {
            $search = array_merge($search, $escapeDn);
        }

        $replace = array();
        foreach ($search as $char) {
            $replace[] = sprintf('\\%02x', ord($char));
        }

        return str_replace($search, $replace, $string);
    }

    /**
     * Always returns false
     *
     * @return false
     */
    public function fetchErrors()
    {
        $this->lastErrorCode = ldap_errno($this->connection);
        $this->lastErrorMessage = ldap_error($this->connection);
        $this->logger->error(
            'Fetched error',
            [
                'code' => $this->lastErrorCode,
                'message' => $this->lastErrorMessage,
                'last_query' => json_encode($this->lastQuery),
            ]
        );
        return false;
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return sprintf('[Code %d] %s', $this->lastErrorCode, $this->lastErrorMessage);
    }

    /**
     * Always returns true
     *
     * @return bool
     */
    public function resetErrors()
    {
        $this->lastErrorCode = 0;
        $this->lastErrorMessage = '';
        return true;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return 0 !== $this->lastErrorCode || '' !== $this->lastErrorMessage;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->logout();
    }

    /**
     * @param $rdn
     * @return string
     */
    protected function expandRdn($rdn)
    {
        $baseDn = $this->settings['baseDn'];

        if (empty($rdn)) {
            return $baseDn;
        }

        $baseDnLength = strlen($baseDn);
        $userDn = substr($rdn, strlen($rdn) - $baseDnLength);

        if (strlen($rdn) < $baseDnLength) {
            $rdn .= ',' . $baseDn;
        } elseif ($userDn !== $baseDn) {
            $rdn .= $baseDn;
        }
        return $rdn;
    }
}
