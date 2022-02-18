<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can
include entry types based on their user group.
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2022 Foster Commerce
 */

/**
 * Entry Type Lock config.php
 *
 * This file exists only as a template for the Entry Type Lock settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'entry-type-lock.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    'sections' => [
        /*
        // Add each section you want to lock down entry types for
        'pages' => [
            // Add each entry type in the section you want to lock (by limit and/or user group)
            'contactPage' => [
                'limit' => 1,
                'userGroups' => [
                    'adminGroup1',
                    'adminGroup2'
                ]
            ],
            'blogLanding' => [
                'limit' => 1
            ]
        ],
        'blog' => [
            'pressRelease' => [
                'userGroups' => ['adminGroup1']
            ]
        ]
        */
    ]
];
