<?php
namespace In2code\In2connector\Driver;

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

        if (true === (bool)$this->settings['ldaps']) {
            $hostname = self::LDAPS_PROTOCOL . $this->settings['hostname'];
        } else {
            $hostname = $this->settings['hostname'];
        }
        $connection = ldap_connect($hostname, $this->settings['port']);

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
                $this->getLogger()->error(
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
            $this->getLogger()->error(
                'The protocol version ' . $this->settings['protocolVersion'] . ' was not accepted',
                ['errorCode' => $errorCode, 'errorMessage' => $errorMessage, 'file' => $file, 'line' => $line]
            );
            return true;
        } elseif ($errorMessage === 'ldap_list(): Search: No such object') {
            $this->lastErrorCode = self::ERROR_SEARCH_FAILED;
            $this->lastErrorMessage = $this->translate('driver.ldap.search_failed');
            $this->getLogger()->error(
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
            $this->getLogger()->error(
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
        $success = false;
        if (!is_resource($this->connection)) {
            if (true === (bool)$this->settings['ldaps']) {
                $hostName = self::LDAPS_PROTOCOL . $this->settings['hostname'];
            } else {
                $hostName = $this->settings['hostname'];
            }
            $this->connection = ldap_connect($hostName, (int)$this->settings['port']);
            if (false === $this->connection) {
                $this->getLogger()->error(
                    sprintf('Connection to "%s" on port [%d] failed', $hostName, $this->settings['port'])
                );
            } else {
                $this->getLogger()->info(
                    sprintf('Successful connected to "%s" on port [%d]', $hostName, $this->settings['port'])
                );
            }
            ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, $this->settings['timeout']);
            if ('NULL' !== $this->settings['protocolVersion']) {
                ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, (int)$this->settings['protocolVersion']);
            }
            $success = ldap_bind($this->connection, $this->settings['username'], $this->settings['password']);
            if (false === $success) {
                $this->getLogger()->error(
                    sprintf('Authentication to LDAP failed for user "%s"', $this->settings['username'])
                );
            } else {
                $this->getLogger()->info(
                    sprintf('Authentication to LDAP successful for user "%s"', $this->settings['username'])
                );
            }
        }
        return $success;
    }

    /**
     *
     */
    public function __destruct()
    {
        if (is_resource($this->connection)) {
            ldap_unbind($this->connection);
        } else {
            unset($this->connection);
        }
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @return resource
     */
    public function listDirectory($distinguishedName, $filter)
    {
        $this->initialize();
        return ldap_list($this->connection, $distinguishedName, $filter);
    }

    /**
     * @param resource $resource
     * @return array
     */
    public function getResults($resource)
    {
        $this->initialize();
        return ldap_get_entries($this->connection, $resource);
    }

    /**
     * @param resource $resource
     * @return int
     */
    public function countResults($resource)
    {
        $this->initialize();
        return ldap_count_entries($this->connection, $resource);
    }

    /**
     * @param resource $resource
     * @return bool
     */
    public function freeResult($resource)
    {
        return ldap_free_result($resource);
    }

    /**
     * @param string $distinguishedName
     * @param string $filter
     * @return resource
     */
    public function search($distinguishedName, $filter)
    {
        $this->initialize();
        return ldap_search($this->connection, $distinguishedName, $filter);
    }

    /**
     * @param resource $entry
     * @return string
     */
    public function getDnOfEntry($entry)
    {
        $this->initialize();
        return ldap_get_dn($this->connection, $entry);
    }

    /**
     * @param $distinguishedName
     * @return bool
     */
    public function delete($distinguishedName)
    {
        $this->initialize();
        return ldap_delete($this->connection, $distinguishedName);
    }

    /**
     * @param string $distinguishedName
     * @param array $values
     * @return bool
     */
    public function modify($distinguishedName, array $values)
    {
        foreach ($values as $key => $unescaped) {
            $values[$key] = $this->escape($unescaped);
        }
        $this->initialize();
        return ldap_modify($this->connection, $distinguishedName, $values);
    }

    /**
     * @param $distinguishedName
     * @param array $values
     * @return bool
     */
    public function add($distinguishedName, array $values)
    {
        foreach ($values as $key => $unescaped) {
            $values[$key] = $this->escape($unescaped);
        }
        $this->initialize();
        return ldap_add($this->connection, $distinguishedName, $values);
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
     *
     */
    public function logout()
    {
        if (is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }
    }

    /**
     * @param string $distinguishedName
     * @param string $password
     * @return bool
     */
    public function testLogin($distinguishedName, $password)
    {
        $settings = $this->settings;
        $settings['username'] = $distinguishedName;
        $settings['password'] = $password;

        $ldapDriver = clone $this;
        $ldapDriver->setSettings($settings);
        $ldapDriver->logout();
        $result = $ldapDriver->initialize();
        $ldapDriver->logout();
        return $result;
    }

    /**
     * Escapes LDAP Characters and Prevents LDAP Injection
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
     * @param Boolean $dn
     * @return String
     */
    protected function escape($string, $dn = null)
    {
        $escapeDn = array('\\', '*', '(', ')', "\x00");
        $escape = array('\\', ',', '=', '+', '<', '>', ';', '"', '#');

        $search = array();
        if ($dn === null) {
            $search = array_merge($search, $escapeDn, $escape);
        } elseif ($dn === false) {
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
}
