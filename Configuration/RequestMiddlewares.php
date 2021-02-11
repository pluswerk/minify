<?php

/***
 *
 * This file is part of an "Pluswerk AG" Extension.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 5/20/19 2:37 PM Stefan Lamm <stefan.lamm@pluswerk.ag>, Pluswerk AG
 *
 ***/
/** @noinspection PhpFullyQualifiedNameUsageInspection */
return [
    'frontend' => [
        'minify/service/htmlminifier' => [
            'target' => \Pluswerk\PlusMinify\Hook\HtmlMinifier::class,
            'after' => [
            ],
            'before' => [
                'typo3/cms-adminpanel/renderer',
                'typo3/cms-frontend/output-compression'
            ]
        ]
    ]
];
