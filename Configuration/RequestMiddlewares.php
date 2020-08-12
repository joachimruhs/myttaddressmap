<?php


return [
    'frontend' => [
        'wsr/myttaddressmap/map-utilities' => [
			'disabled' => false,
            'target' => \WSR\Myttaddressmap\Middleware\MapUtilities::class,
            'before' => [
			],
            'after' => [
				'typo3/cms-frontend/tsfe'
            ],
        ],
    ]
];

