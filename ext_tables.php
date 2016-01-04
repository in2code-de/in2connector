<?php

defined('TYPO3_MODE') or die('hard');

if (!isset($_EXTKEY)) {
    $_EXTKEY = 'in2connector';
}

if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
    call_user_func(
        function ($extKey) {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.' . $extKey,
                'tools',
                'mod1',
                '',
                array(
                    'Dashboard' => \In2code\In2connector\Controller\DashboardController::getModuleActions(),
                    'LdapConnection' => \In2code\In2connector\Controller\LdapConnectionController::getModuleActions(),
                ),
                array(
                    'access' => 'user,group',
                    'icon' => 'EXT:t3skin/icons/gfx/i/module.gif',
                    'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf',
                )
            );
        },
        $_EXTKEY
    );
}

$configuration = new \In2code\In2connector\Domain\Model\Configuration();

$GLOBALS['TYPO3_CONF_VARS']['LOG']['In2code']['In2connector']['writerConfiguration'] = [
    $configuration->getLogLevel() => array(
        \TYPO3\CMS\Core\Log\Writer\DatabaseWriter::class => array(
            'logTable' => 'tx_in2connector_log',
        ),
    ),
];
