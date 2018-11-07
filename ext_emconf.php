<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'in2connector',
    'description' => 'Enterprise Level Connection Manager for LDAP, SOAP etc.',
    'category' => 'module',
    'author' => 'Oliver Eglseder',
    'author_email' => 'oliver.eglseder@in2code.de',
    'state' => 'stable',
    'author_company' => 'in2code GmbH',
    'version' => '1.2.8',
    'constraints' => [
        'depends' => [
            'typo3' => '7.0.0-7.99.99',
            'extbase' => '7.0.0-7.99.99',
            'fluid' => '7.0.0-7.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
