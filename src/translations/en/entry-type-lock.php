<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can
 * include entry types based on their user group.
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2022 Foster Commerce
 */

/**
 * Entry Type Lock en Translation
 *
 * Returns an array with the string to be translated (as passed to `Craft::t('entry-type-lock', '...')`) as
 * the key, and the translation as the value.
 *
 * http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html
 *
 * @author    Foster Commerce
 * @package   EntryTypeLock
 * @since     1.0.0
 */
return [
    'Entry Type Lock' => 'Entry Type Lock',
    'Edit Plugin Settings' => 'Edit Plugin Settings',
    '{name} plugin loaded' => '{name} plugin loaded',
    'Settings' => 'Settings',
    'Warning' => 'Warning',
    'The Entry Type Lock plugin settings are being overridden in the \'config/entry-type-lock.php\' file.' => 'The Entry Type Lock plugin settings are being overridden in the \'config/entry-type-lock.php\' file.',
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
