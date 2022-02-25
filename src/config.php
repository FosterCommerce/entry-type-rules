<?php
/**
 * Entry Type Rules plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to set rules on number of entry types in a Craft section and/or limit who can
 * include entry type entries based on their user group.
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2022 Foster Commerce
 */

/**
 * Entry Type Rules config.php
 *
 * This file exists only as a template for the Entry Type Rules settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'entry-type-rules.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    'sections' => [
        /*
        // Add each section you want to create entry type rules for
        'pages' => [
            // Add each entry type in the section you want to create rules for (by limit and/or user group)
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
