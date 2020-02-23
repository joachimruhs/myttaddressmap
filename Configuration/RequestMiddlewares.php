<?php


return [
    'frontend' => [
        'typo3/cms-frontend/eid' => [
            'disabled' => true,
        ],		
        'wsr/myttaddressmap/map-utilities' => [
			'disabled' => false,
            'target' => \WSR\Myttaddressmap\Middleware\MapUtilities::class,
            'before' => [
//				'typo3/cms-frontend/prepare-tsfe-rendering'
//				'typo3/cms-frontend/shortcut-and-mountpoint-redirect'
//			'typo3/cms-frontend/content-length-headers'
			],
            'after' => [
//				'typo3/cms-frontend/prepare-tsfe-rendering'
				'typo3/cms-frontend/tsfe'
            ],
        ],
    ]
];

