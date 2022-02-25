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
 * Entry Type Rules en Translation
 *
 * Returns an array with the string to be translated (as passed to `Craft::t('entry-type-rules', '...')`) as
 * the key, and the translation as the value.
 *
 * http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
return [
    'Entry Type Rules' => 'Entry Type Rules',
    'Edit Plugin Settings' => 'Edit Plugin Settings',
    '{name} plugin loaded' => '{name} plugin loaded',
    'Settings' => 'Settings',
    'Warning' => 'Warning',
    'The Entry Type Rules plugin settings are being overridden in the \'config/entry-type-rules.php\' file.' => 'The Entry Type Rules plugin settings are being overridden in the \'config/entry-type-rules.php\' file.',
    'Section' => 'Section',
    'Entry Type' => 'Entry Type',
    'The entry type and its current total entry count.' => 'The entry type and its current total entry count.',
    'Limit' => 'Limit',
    'Limit the number of entries for the entry type.' => 'Limit the number of entries for the entry type.',
    'Leave blank or set to zero to remove limits for the entry type.' => 'Leave blank or set to zero to disable limits for the entry type.',
    'User Groups' => 'User Groups',
    'Limit which user groups can add new entries for the entry type.' => 'Limit which user groups can add new entries for the entry type.',
    'Admin users will always be able to add new entries.' => 'Admin users will always be able to add new entries.',
    'Entries' => 'Entries'
];
