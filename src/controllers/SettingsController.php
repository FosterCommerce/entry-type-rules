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

		$variables = [];

		$siteHandleUri = Craft::$app->isMultiSite ? '/' . $siteHandle : '';

		$overrides = Craft::$app->getConfig()->getConfigFromFile('entry-type-rules');
		// dd(Plugin::$plugin?->getSettings());

		$variables = [
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

		/** @var Settings $oldSettings  */
		$oldSettings = $plugin->getSettings();

		$mergedSettings = ArrayHelper::merge($oldSettings->toArray()['sections'] ?? [], $newSettings ?? []);


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
}
