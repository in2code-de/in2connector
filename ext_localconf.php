<?php

if (defined('TYPO3_MODE')) {
    if (!defined('TX_IN2CONNECTOR')) {
        define('TX_IN2CONNECTOR', 'tx_in2connector');
        define('TX_IN2CONNECTOR_DRIVER_LDAP', 'ldap');
        define('TX_IN2CONNECTOR_DRIVER_SOAP', 'soap');
    }

    call_user_func(
        function () {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
                \In2code\In2connector\Property\TypeConverter\ArrayToStringConverter::class
            );
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
                \In2code\In2connector\Property\TypeConverter\StringToConnectionConverter::class
            );

            // get configuration service
            $configurationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \In2code\In2connector\Service\ConfigurationService::class
            );

            //configure logger
            $GLOBALS['TYPO3_CONF_VARS']['LOG']['In2code']['In2connector']['writerConfiguration'] = [
                $configurationService->getLogLevel() => array(
                    \TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => array(
                        'logTable' => 'tx_in2connector_log',
                    ),
                ),
            ];

            // register driver
            $connectionRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \In2code\In2connector\Registry\ConnectionRegistry::class
            );
            if (!$connectionRegistry->hasRegisteredDriver(TX_IN2CONNECTOR_DRIVER_LDAP)) {
                $connectionRegistry->registerDriver(
                    TX_IN2CONNECTOR_DRIVER_LDAP,
                    \In2code\In2connector\Driver\LdapDriver::class,
                    'Driver/Ldap/Forms/Settings'
                );
            }
            if (!$connectionRegistry->hasRegisteredDriver(TX_IN2CONNECTOR_DRIVER_SOAP)) {
                $connectionRegistry->registerDriver(
                    TX_IN2CONNECTOR_DRIVER_SOAP,
                    \In2code\In2connector\Driver\SoapDriver::class,
                    'Driver/Soap/Forms/Settings'
                );
            }
        }
    );
}
