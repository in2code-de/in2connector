<?php

if (defined('TYPO3_MODE') && TYPO3_MODE === 'BE') {
    call_user_func(
        function () {
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'In2code.In2connector',
                'tools',
                'mod1',
                '',
                [
                    'Connection' => 'index,newFromDemand,new,create,configure,setConfig,delete',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:t3skin/icons/gfx/i/module.gif',
                    'labels' => 'LLL:EXT:in2connector/Resources/Private/Language/locallang.xlf',
                ]
            );
        }
    );
}

