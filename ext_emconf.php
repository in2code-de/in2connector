<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'in2connector',
    'description' => 'Enterprise Level Connection Manager for LDAP, SOAP etc.',
    'category' => 'module',
    'author' => 'Oliver Eglseder',
    'author_email' => 'oliver.eglseder@in2code.de',
    'state' => 'stable',
    'author_company' => 'in2code GmbH',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.0.0-8.7.99',
            'extbase' => '7.0.0-8.7.99',
            'fluid' => '7.0.0-8.7.99',
            'logs' => '1.2.0-1.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
