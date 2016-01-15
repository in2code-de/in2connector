<?php

if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {

    // Extkey fallback
    if (!isset($_EXTKEY)) {
        $_EXTKEY = 'in2connector';
    }

    // boot the extension
    call_user_func(
        function ($extKey) {
            // register backend module
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.' . $extKey,
                'tools',
                'mod1',
                '',
                [
                    'Dashboard' => \In2code\In2connector\Controller\DashboardController::getModuleActions(),
                    'Configuration' => \In2code\In2connector\Controller\ConfigurationController::getModuleActions(),
                    'Connection' => \In2code\In2connector\Controller\ConnectionController::getModuleActions(),
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

