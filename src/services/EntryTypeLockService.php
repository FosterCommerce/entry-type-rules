<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can include entry types based on their user group
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2021 Foster Commerce
 */

namespace fostercommerce\entrytypelock\services;

use fostercommerce\entrytypelock\EntryTypeLock;

use Craft;
use craft\base\Component;
use craft\elements\Entry;

/**
 * EntryTypeLockService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Foster Commerce
 * @package   EntryTypeLock
 * @since     1.0.0
 */
class EntryTypeLockService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     EntryTypeLock::$plugin->entryTypeLockService->exampleService()
     *
     * @return array
     */
    public function getLockedEntryTypes($sectionId)
    {
        // We will return an array of locked entry type IDs
        $lockedEntryTypes = [];

        // Get the plugins settings
        $settings = EntryTypeLock::$plugin->getSettings();

        // Get all the entry types for this section into an array
        $sectionEntryTypes = Craft::$app->sections->getEntryTypesBySectionId($sectionId);
        $entryTypesIdsMap = [];
        foreach ($sectionEntryTypes as $entryType) {
            $entryTypesIdsMap[$entryType->handle] = (int) $entryType->id;
        }

        // Get the section handle we are dealing with
        $sectionHandle = Craft::$app->sections->getSectionById($sectionId)->handle;

        // Get the settings for this section
        $lockedTypesSettings = isset($settings['sections'][$sectionHandle]) ? $settings['sections'][$sectionHandle] : [];

        // Loop through the locked entry type settings
        foreach ($lockedTypesSettings as $typeHandle => $limit) {
            // Get the count of each entry type and compare it to the limit value
            $entryCount = Entry::find()->sectionId($sectionId)->type($typeHandle)->count();
            if ($entryCount >= $limit) {
                array_push($lockedEntryTypes, $entryTypesIdsMap[$typeHandle]);
            }
        }

        return $lockedEntryTypes;
    }
}
