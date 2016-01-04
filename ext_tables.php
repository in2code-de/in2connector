<?php

if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
    // define extension registry namespace
    if (!defined('TX_IN2CONNECTOR')) {
        define('TX_IN2CONNECTOR', 'tx_in2connector');
        define('TX_IN2CONNECTOR_DRIVER_LDAP', 'ldap');
    }

    // Extkey fallback
    if (!isset($_EXTKEY)) {
        $_EXTKEY = 'in2connector';
    }

    // boot the extension
    call_user_func(
        function ($extKey) {
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
            $connectionRegistry->registerDriver(
                TX_IN2CONNECTOR_DRIVER_LDAP,
                \In2code\In2connector\Driver\LdapDriver::class
            );

            // register backend module
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.' . $extKey,
                'tools',
                'mod1',
                '',
                [
                    'Dashboard' => \In2code\In2connector\Controller\DashboardController::getModuleActions(),
                    'Configuration' => \In2code\In2connector\Controller\ConfigurationController::getModuleActions(),
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:t3skin/icons/gfx/i/module.gif',
                    'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf',
                ]
            );
        },
        $_EXTKEY
    );
}

