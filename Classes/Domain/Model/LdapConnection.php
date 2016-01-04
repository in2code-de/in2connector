<?php
namespace In2code\In2connector\Domain\Model;

/**
 * Class LdapConnection
 */
class LdapConnection extends AbstractConnection
{
    /**
     * @var resource|null
     */
    protected $connection = null;

    /**
     * @var string
     */
    protected $hostname = '';

    /**
     * @var int
     */
    protected $port = 0;

    /**
     * LDAP RDNs (Relative Distinguished Names) or DN (Distinguished Name)
     *
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var int
     */
    protected $protocolVersion = 3;

    /**
     * @var string
     */
    protected $baseDn = '';

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param int $protocolVersion
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * @param string $baseDn
     */
    public function setBaseDn($baseDn)
    {
        $this->baseDn = $baseDn;
    }

    /**
     * @param $errorLevel
     * @param $errorMessage
     * @param $errorFile
     * @param $errorLine
     * @param $context
     */
    public function handleError($errorLevel, $errorMessage, $errorFile, $errorLine, $context)
    {
        if ($errorMessage === 'ldap_bind(): Unable to bind to server: Protocol error') {
            $this->connectionStatus = self::CONNECTION_STATUS_ERROR;
            $this->connectionError = $errorMessage;
        } else {
            $this->connectionStatus = $errorLevel;
            $this->connectionError = $errorMessage;
        }
    }

    /**
     *
     */
    protected function connect()
    {
        set_error_handler([$this, 'handleError']);
        try {
            if ($this->uid > 0) {
                $this->connection = ldap_connect($this->hostname, $this->port);
                if (is_resource($this->connection)) {
                    $this->connectionStatus = self::CONNECTION_STATUS_OK;

                    $username = $this->username ?: null;
                    $password = $this->password ?: null;

                    ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, (int)$this->protocolVersion);

                    if (!ldap_bind($this->connection, $username, $password)) {
                    }
                } elseif (false === $this->connection) {
                    $this->connectionStatus = self::CONNECTION_STATUS_ERROR;
                    $this->connectionError = 'Could not connect to the given hostname and port';
                }
            } else {
                $this->connectionStatus = self::CONNECTION_STATUS_WARNING;
                $this->connectionError = 'Connection is not defined yet';
            }
        } catch (\Exception $e) {
            $this->connectionStatus = self::CONNECTION_STATUS_ERROR;
            $this->connectionError = $e->getMessage();
        }
        restore_error_handler();
    }

    /**
     * @return bool|void
     */
    protected function disconnect()
    {
        if (is_resource($this->connection)) {
            return ldap_unbind($this->connection);
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isConnected()
    {
        return is_resource($this->connection);
    }

    protected function connectIfNeccessary()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
    }

    /**
     * @return array
     */
    public function listDirectory()
    {
        $this->connectIfNeccessary();
        $result = ldap_list($this->connection, $this->baseDn, 'ou=*');
        return ldap_get_entries($this->connection, $result);
    }
}
