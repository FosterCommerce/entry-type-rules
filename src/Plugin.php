<?php

namespace fostercommerce\entrytyperules;

use Craft;
use craft\base\Element;
use craft\base\Model;

use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\helpers\ConfigHelper;
use craft\web\Controller;
use craft\web\UrlManager;
use craft\web\View;
use fostercommerce\entrytyperules\assetbundles\entrytyperules\EntryTypeRulesAsset;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\services\Service;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * @property  Service $service
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class Plugin extends BasePlugin
{

    public static ?Plugin $plugin;

	public bool $hasCpSettings = true;

	public static ?Settings $settings = null;

	/**
	 * @throws InvalidConfigException
	 */
	public function init(): void
	{
		parent::init();
		self::$plugin = $this;

		/** @var Settings $settings */
        $settings = self::$plugin->getSettings();
		self::$settings = $settings;

		$this->setComponents([
			'service' => Service::class,
		]);

		Event::on(
			Element::class,
			Element::EVENT_DEFINE_SIDEBAR_HTML,
			function (DefineHtmlEvent $event): void {
				$element = $event->sender;
				// If the element is a Craft Entry
				if ($element instanceof Entry) {
					// Get the section ID and section type the entry belongs to
					$sectionId = $element->section?->id;
					// If it is not a single, inject out fields and register the slideout bundle
					if ($element->section?->type !== 'single') {
						// Get the views namespace
						$viewNamespace = Craft::$app->getView()->namespace;
						// Create the elements we are going to inject (Note: the ID's will automatically be namespaced for the view by Craft)
						$injectedHtml = "<input type=\"hidden\" id=\"entryTypeRulesSectionId\" value=\"{$sectionId}\"/>";
						// Inject the elements, our asset bundle, and start it up with some JS
						$event->html = $injectedHtml . $event->html;
						Craft::$app->getView()->registerAssetBundle(EntryTypeRulesAsset::class, View::POS_END);
						Craft::$app->getView()->registerJs('new Craft.EntryTypeRules("' . $viewNamespace . '");', View::POS_READY);
					}
				}
			}
		);


		Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                // Register Control Panel routes
                $event->rules = array_merge(
                    $event->rules,
					[
                    	'entry-type-rules' => 'entry-type-rules/settings',
						// 'entry-type-rules/<siteHandle>' => 'entry-type-rules/settings',
					],
                );
            }
        );
	}

	public function getSettingsResponse(): mixed
	{
		// $site = Craft::$app->request->getParam('site');

		// $overrides = Craft::$app->getConfig()->getConfigFromFile($this->handle);

		// /** @var Controller $controller */
		// $controller = Craft::$app->controller;
		// return $controller->renderTemplate(
		// 	'entry-type-rules/settings',
		// 	[
		// 		'settings' => $this->getSettings(),
		// 		'overrides' => ConfigHelper::localizedValue($overrides, $site),
		// 		'sectionsUrl' => ConfigHelper::localizedValue(UrlHelper::cpUrl('settings/sections', $site)),
		// 		'entriesUrl' => ConfigHelper::localizedValue(UrlHelper::cpUrl('entries', $site)),
		// 	]
		// );
		return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('entry-type-rules/settings'));
	}

	protected function createSettingsModel(): ?Model
	{
		return new Settings();
	}
}
