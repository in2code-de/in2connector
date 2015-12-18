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
                    'Dashboard' => 'index',
                    'Connection' => 'add',
                ),
                array(
                    'access' => 'user,group',
                    'icon' => 'EXT:' . $extKey . '/ext_icon.gif',
                    'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang.xlf',
                )
            );
        },
        $_EXTKEY
    );
}

