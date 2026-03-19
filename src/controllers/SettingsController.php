<?php

namespace fostercommerce\entrytyperules\controllers;

use Craft;

use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\ConfigHelper;
use craft\helpers\Cp;
use craft\helpers\UrlHelper;
use craft\models\Site;
use craft\web\Controller;
use craft\web\Request;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\Plugin;
use Illuminate\Support\Collection;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
	protected array|int|bool $allowAnonymous = [];

	public function actionIndex(): Response
	{
		$site = Cp::requestedSite();
		$siteHandle = $site?->handle;
		$siteId = $site?->id;
		$sections = Craft::$app->getEntries()->getAllSections();

		$enabledSections = array_filter($sections, function($section) use ($siteId) {
			return $section->getSiteSettings()[$siteId]->enabledByDefault ?? false;
		});
		$variables = [];

		$siteHandleUri = Craft::$app->isMultiSite ? '/' . $siteHandle : '';

		$overrides = Craft::$app->getConfig()->getConfigFromFile('entry-type-rules');

		// walk through overrides array,
		// if the value set for 'limit' is not an array then replace it with an array of siteHandles all with same value
		// if the value set for limit is an array and there is a key for '*' then replace it with an array of siteHandles all with same value
		$this->_globalValues($overrides);

		$variables = [
			'sections' => $enabledSections,
			'settings' => Plugin::$plugin?->getSettings()->toArray(),
			'overrides' => $overrides,
			'sectionsUrl' => ConfigHelper::localizedValue(UrlHelper::cpUrl('settings/sections', $siteHandle)),
			'siteHandle' => $siteHandle,
			'siteHandleUri' => $siteHandleUri,
			'siteId' => $siteId,
			'crumbs' => $this->_buildCrumbs(),
		];


		$this->_buildCrumbs();



		/** @var Controller $controller */
		$controller = Craft::$app->controller;
		return $controller->renderTemplate(
			'entry-type-rules/settings',
			$variables
		);
	}

	/**
	 * Handle a request going to our plugin's action URL for saving settings,
	 * e.g.: actions/craft-entry-type-rules/save-settings
	 *
	 * @throws BadRequestHttpException
	 * @throws InvalidConfigException
	 * @throws MissingComponentException
	 * @throws MethodNotAllowedHttpException
	 */
	public function actionSaveSettings(): Response
	{
		$this->requirePostRequest();
		/** @var Request $request */
		$request = Craft::$app->getRequest();

		$siteHandle = Craft::$app->getSites()->getSiteById($request->getBodyParam('siteId'))->handle;

		/** @var Plugin $plugin */
		$plugin = Plugin::getInstance();

		$newSettings = $request->getBodyParam('sections');

		$oldSettings = $plugin->getSettings()->toArray();

		$this->_removeUserGroupsForSite($oldSettings, $siteHandle);


		$mergedSettings = ArrayHelper::merge($oldSettings['sections'] ?? [], $newSettings ?? []);


		$settings = new Settings([
			'sections' => $mergedSettings,
		]);



		if (! $settings->validate() || ! Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray())) {
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));
		} else {
			Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
		}

		return $this->redirectToPostedUrl();
	}


	/**
	 * @return array<int, array<string, array<string, array<int, array<string, mixed>>|string>|string|null>>
	 */
	private function _buildCrumbs(): array
	{
		$sites = Craft::$app->getSites();
		$requestedSite = Cp::requestedSite() ?? Craft::$app->getSites()->getPrimarySite();
		$requestedSiteId = $requestedSite->id;
		$requestedSiteName = $requestedSite->name;

		$siteCrumbItems = [];
		$siteGroups = Craft::$app->getSites()->getAllGroups();
		$crumbSites = Collection::make($sites->getAllSites())
			->map(fn (Site $site) => [
				'site' => $site,
			])
			->keyBy(fn (array $site) => $site['site']->id)
			->all();

		foreach ($siteGroups as $siteGroup) {
			$groupSites = $siteGroup->getSites();

			if (empty($groupSites)) {
				continue;
			}

			$groupSiteItems = array_map(fn (Site $site) => [
				'status' => $crumbSites[$site->id]['site']->status ?? null,
				'label' => Craft::t('site', $site->name),
				'url' => UrlHelper::cpUrl("entry-type-rules?site={$site->handle}"),
				'hidden' => ! isset($crumbSites[$site->id]),
				'selected' => $site->id === $requestedSiteId,
				'attributes' => [
					'data' => [
						'site-id' => $site->id,
					],
				],
			], $groupSites);

			if (count($siteGroups) > 1) {
				$siteCrumbItems[] = [
					'heading' => Craft::t('site', $siteGroup->name),
					'items' => $groupSiteItems,
					'hidden' => ! ArrayHelper::contains($groupSiteItems, fn (array $item) => ! $item['hidden']),
				];
			} else {
				array_push($siteCrumbItems, ...$groupSiteItems);
			}
		}
		// Add in the breadcrumbs
		$crumbs = [
			[
				'id' => 'language-menu',
				'icon' => 'world',
				'label' => Craft::t(
					'site',
					$requestedSiteName
				),
				'menu' => [
					'items' => $siteCrumbItems,
					'label' => Craft::t('site', 'Select site'),
				],
			],
			[
				'label' => Plugin::$plugin?->getPluginName(),
			],
		];

		return $crumbs;
	}


	function _removeUserGroupsForSite(array &$array, string $targetSite, ?string $currentSite = null): void
	{
		foreach ($array as $key => &$value) {
			// Track when we enter a site-specific branch
			$nextSite = $currentSite;
			if ($key === 'firstSite' || $key === 'secondSite') {
				$nextSite = $key;
			}

			// Remove userGroups only if we're inside the target site
			if ($key === 'userGroups' && $currentSite === $targetSite) {
				unset($array[$key]);
				continue;
			}

			if (is_array($value)) {
				$this->_removeUserGroupsForSite($value, $targetSite, $nextSite);
			}
		}
	}


	private function _globalValues(array &$array): void
	{
		// get all sitehandles
		$sites = Craft::$app->getSites()->getAllSites();
		$siteHandles = array_map(function($site) {
			return $site->handle;
		}, $sites);

		foreach ($array as $key => &$value) {
			// if we only have an integer as a limit value, then set the value to be an array of site handles set to the value
			if ($key === 'limit' && !is_array($value)) {
				$limitValue = $value;
				$value = array_fill_keys($siteHandles, $limitValue);
			}

			// if we have an array of limit values, but the array contains the key '*'
			// then set any undefined sites to use the value for '*'
			if ($key === 'limit' and is_array($value)) {
				if(array_key_exists('*', $value)){
					$limitValue = $value['*'];
					$missingSiteHandles = array_values(array_diff($siteHandles, array_keys($value)));
					$defaultedSiteHandles = array_fill_keys($missingSiteHandles, $limitValue);
					$value = array_merge($value, $defaultedSiteHandles);
					// unset the '*'
					unset($value['*']);
				}
			}

			//TODO global settings for usergroups


			if (is_array($value)) {
				$this->_globalValues($value);
			}
		}
	}
}
