<?php

use Pluswerk\PlusMinify\Middleware\MinifyMiddleware;

return [
    'frontend' => [
        'minify/service/htmlminifier' => [
            'target' => MinifyMiddleware::class,
            // in the request direction it is after these middlewares:
            // but in response direction it is before these middlewares:
            'after' => [
                'typo3/cms-frontend/output-compression',
                'typo3/cms-frontend/content-length-headers',
            ],
            // in the request direction it is before these middlewares:
            // but in response direction it is after these middlewares:
            'before' => [
                'typo3/cms-adminpanel/renderer',
            ]
        ]
    ]
];
