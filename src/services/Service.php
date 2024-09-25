<?php

namespace fostercommerce\entrytyperules\services;

use Craft;

use craft\base\Component;
use craft\elements\Entry;
use craft\web\User;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\Plugin;

class Service extends Component
{
	/**
	 * @return array<int, int>
	 * @throws \Throwable
	 */
	public function getLockedEntryTypes(int $sectionId, User $user): array
	{
		// We will return an array of locked entry type IDs
		$lockedEntryTypes = [];

		// Get the plugins settings
		/** @var Settings $settings */
		$settings = Plugin::getInstance()?->getSettings();

		// Get all the entry types for this section into an array
		$sectionEntryTypes = Craft::$app->getEntries()->getEntryTypesBySectionId($sectionId);
		$entryTypesIdsMap = [];
		foreach ($sectionEntryTypes as $sectionEntryType) {
			$entryTypesIdsMap[$sectionEntryType->handle] = (int) $sectionEntryType->id;
		}

		// Get the section handle we are dealing with
		$sectionHandle = Craft::$app->getEntries()->getSectionById($sectionId)?->handle;
		if ($sectionHandle === null) {
			return [];
		}

		// Get the settings for this section
		$lockedTypesSettings = $settings->sections[$sectionHandle] ?? [];

		// Get the current user groups
		$userGroups = $user->getIdentity()?->getGroups() ?? [];
		$userGroupArray = [];
		foreach ($userGroups as $userGroup) {
			$userGroupArray[] = $userGroup->handle;
		}

		// Loop through the locked entry type settings
		foreach ($lockedTypesSettings as $typeHandle => $setting) {
			// Get the count of each entry type and compare it to the limit value
			$limit = $setting['limit'] ?? 0;
			if ($limit > 0) {
				$entryCount = Entry::find()->sectionId($sectionId)->type($typeHandle)->count();
				if ($entryCount >= $setting['limit']) {
					$lockedEntryTypes[] = $entryTypesIdsMap[$typeHandle];
				}
			}

			// Check the users groups against the userGroup setting
			if (isset($setting['userGroups']) && is_array($setting['userGroups'])) {
				$matchedGroups = array_intersect($setting['userGroups'], $userGroupArray);

				if ($matchedGroups === [] && ! $user->getIsAdmin()) {
					$lockedEntryTypes[] = $entryTypesIdsMap[$typeHandle];
				}
			}
		}

		return array_unique($lockedEntryTypes);
	}
}
