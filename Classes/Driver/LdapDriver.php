<?php
namespace In2code\In2connector\Driver;

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
    const TEST_PROTOCOL_VERSION_EMPTY = 102;
    const TEST_CONNECTION_FAILED = 103;
    const TEST_COULD_NOT_SET_PROTOCOL_VERSION = 104;
    const TEST_WRONG_CREDENTIALS = 105;
    const TEST_PROTOCOL_VERSION_MISMATCH = 106;
    const TEST_BASE_DN_NOT_READABLE = 107;
    const TEST_SERVER_UNREACHABLE = 201;
    const ERROR_SEARCH_FAILED = 201;

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
//            $connection = ldap_connect(self::LDAPS_PROTOCOL . $this->settings['hostname'], $this->settings['port']);
            $connection = ldap_connect('ldaps://localhost:10635');
        } else {
            $connection = ldap_connect($this->settings['hostname'], $this->settings['port']);
        }
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

        if (!isset($this->settings['protocolVersion'])) {
            $this->lastErrorCode = self::TEST_PROTOCOL_VERSION_EMPTY;
            $this->lastErrorMessage = $this->translate('driver.ldap.test.protocol_version_empty');
            $this->captureErrors(false);
            return false;
        }

        if (!ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, (int)$this->settings['protocolVersion'])) {
            $this->lastErrorCode = self::TEST_COULD_NOT_SET_PROTOCOL_VERSION;
            $this->lastErrorMessage = $this->translate('driver.ldap.test.protocol_version_not_settable');
            $this->captureErrors(false);
            return false;
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
        } elseif('ldap_bind(): Unable to bind to server: Can\'t contact LDAP server' === $errorMessage) {
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
}
