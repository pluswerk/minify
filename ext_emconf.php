<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => '+Pluswerk: Minify',
    'description' => 'Minifies your html at a glace',
    'category' => 'service',
    'author' => 'Stefan Lamm',
    'author_email' => 'stefan.lamm@pluswerk.ag',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.1.0',
    'constraints' =>[
        'depends' => [
            'typo3' => '10.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
