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
                    'Connection' => \In2code\In2connector\Controller\ConnectionController::getModuleActions(),
                    'Configuration' => 'edit,update',
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

