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

namespace fostercommerce\entrytyperules\services;

use Craft;

use craft\base\Component;
use craft\elements\Entry;
use fostercommerce\entrytyperules\EntryTypeRules;
use yii\base\InvalidConfigException;

/**
 * EntryTypeRulesService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
class EntryTypeRulesService extends Component
{
	// Public Methods
	// =========================================================================

	/**
	 * This function can literally be anything you want, and you can have as many service
	 * functions as you want
	 *
	 * From any other plugin file, call it like this:
	 *
	 *     EntryTypeRules::$plugin->entryTypeRulesService->getLockedEntryTypes($sectionId)
	 */
	public function getLockedEntryTypes($sectionId): mixed
	{
		// We will return an array of locked entry type IDs
		$lockedEntryTypes = [];

		// Get the plugins settings
		$settings = EntryTypeRules::$plugin->getSettings();

		// Get all the entry types for this section into an array
		$sectionEntryTypes = Craft::$app->getEntries()->getEntryTypesBySectionId($sectionId);
		$entryTypesIdsMap = [];
		foreach ($sectionEntryTypes as $entryType) {
			$entryTypesIdsMap[$entryType->handle] = (int) $entryType->id;
		}

		// Get the section handle we are dealing with
		$sectionHandle = Craft::$app->getEntries()->getSectionById($sectionId)->handle;

		// Get the settings for this section
		$lockedTypesSettings = isset($settings['sections'][$sectionHandle]) ? $settings['sections'][$sectionHandle] : [];

		// Get the current user groups
		$user = Craft::$app->getUser();
		$userGroups = $user->getIdentity()->getGroups();
		$userGroupArray = [];
		foreach ($userGroups as $group) {
			array_push($userGroupArray, $group->handle);
		}

		// Loop through the locked entry type settings
		foreach ($lockedTypesSettings as $typeHandle => $setting) {
			// Get the count of each entry type and compare it to the limit value
			if (isset($setting['limit'])) {
				$entryCount = Entry::find()->sectionId($sectionId)->type($typeHandle)->count();
				if ($entryCount >= $setting['limit']) {
					array_push($lockedEntryTypes, $entryTypesIdsMap[$typeHandle]);
				}
			}

			// Check the users groups against the userGroup setting
			if (isset($setting['userGroups']) && is_array($setting['userGroups'])) {
				$matchedGroups = array_intersect($setting['userGroups'], $userGroupArray);

				if (! $matchedGroups && ! $user->getIsAdmin()) {
					array_push($lockedEntryTypes, $entryTypesIdsMap[$typeHandle]);
				}
			}
		}

		return array_unique($lockedEntryTypes);
	}

	/**
	 * This function formats the settings form parameters and converts them into the array
	 * structure required by the plugins settings
	 *
	 * From any other plugin file, call it like this:
	 *
	 *     EntryTypeRules::$plugin->entryTypeRulesService->formatSectionsSettings()
	 *
	 * @throws InvalidConfigException
	 */
	public function formatSectionsSettings($formParams): array
	{
		// $sections = [];

		// foreach ($formParams as $key => $value) {
		//     if (str_contains($key, 'entryType_') and ($value !== '' and $value !== '0')) {
		//         preg_match('/entryType_([0-9]+)_/', $key, $m);
		//         $entryType = Craft::$app->getEntries()->getEntryTypeById((int)$m[1]);
		//         $section = $entryType->getSection();
		//         $paramParts = explode('_', $key);
		//         $param = end($paramParts);
		//         $sections[$section->handle][$entryType->handle][$param] = (is_numeric($value) ? (int) $value : $value);
		//     }
		// }

		return $formParams['sections'];
	}
}
