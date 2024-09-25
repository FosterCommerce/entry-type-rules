<?php

namespace fostercommerce\entrytyperules;

use Craft;
use craft\base\Element;
use craft\base\Model;

use craft\base\Plugin;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\web\View;
use fostercommerce\entrytyperules\assetbundles\entrytyperules\EntryTypeRulesAsset;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\services\EntryTypeRulesService as EntryTypeRulesServiceService;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * @property  EntryTypeRulesServiceService $entryTypeRulesService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class EntryTypeRules extends Plugin
{
	public bool $hasCpSettings = true;

	/**
	 * @throws InvalidConfigException
	 */
	public function init(): void
	{
		parent::init();

		Craft::setAlias('@plugin', $this->getBasePath());
		//
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

		/**
		 * Logging in Craft involves using one of the following methods:
		 *
		 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
		 * Craft::info(): record a message that conveys some useful information.
		 * Craft::warning(): record a warning message that indicates something unexpected has happened.
		 * Craft::error(): record a fatal error that should be investigated as soon as possible.
		 *
		 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
		 *
		 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
		 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
		 *
		 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
		 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
		 *
		 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
		 */
		Craft::info(
			Craft::t(
				'entry-type-rules',
				'{name} plugin loaded',
				[
					'name' => $this->name,
				]
			),
			__METHOD__
		);
	}

	/**
	 * Intercepts the plugin settings page response so we can check the config override file
	 * (if it exists) and so we can process the Post response in our own settings controller method
	 * instead of using the general Craft settings HTML method to render the settings page.
	 * @inheritdoc
	 */
	public function getSettingsResponse(): mixed
	{
		$overrides = Craft::$app->getConfig()->getConfigFromFile($this->handle);

		/** @var \craft\web\Controller $controller */
		$controller = Craft::$app->controller;
		return $controller->renderTemplate(
			'entry-type-rules/settings',
			[
				'settings' => $this->getSettings(),
				'overrides' => $overrides,
			]
		);
	}

	protected function createSettingsModel(): ?Model
	{
		return new Settings();
	}
}
