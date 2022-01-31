<?php

return [
    'frontend' => [
        'minify/service/htmlminifier' => [
            'target' => \Pluswerk\PlusMinify\Middleware\MinifyMiddleware::class,
            'after' => [
            ],
            'before' => [
                'typo3/cms-adminpanel/renderer',
                'typo3/cms-frontend/output-compression'
            ]
        ]
    ]
];
