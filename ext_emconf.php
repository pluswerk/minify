<?php

use Composer\InstalledVersions;

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => '+Pluswerk: Minify',
    'description' => 'Minifies your html at a glace',
    'category' => 'service',
    'author' => 'Stefan Lamm',
    'author_email' => 'stefan.lamm@pluswerk.ag',
    'state' => 'stable',
    'version' => InstalledVersions::getPrettyVersion('pluswerk/minify'),
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0 - 12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
