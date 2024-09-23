<?php
/**
 * Entry Type Rules plugin for Craft CMS 5.x
 *
 * A Craft plugin that allows you to set rules on number of entry types in a Craft section and/or limit who can
 * include entry type entries based on their user group.
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2022 Foster Commerce
 */

namespace fostercommerce\entrytyperules;

use Craft;
use craft\base\Element;
use craft\base\Model;

use craft\base\Plugin;
use craft\events\DefineHtmlEvent;
use craft\services\Plugins;
use craft\web\View;
use fostercommerce\entrytyperules\assetbundles\entrytyperules\EntryTypeRulesAsset;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\services\EntryTypeRulesService as EntryTypeRulesServiceService;
use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 *
 * @property  EntryTypeRulesServiceService $entryTypeRulesService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class EntryTypeRules extends Plugin
{
	// Static Properties
	// =========================================================================

	/**
	 * Static property that is an instance of this plugin class so that it can be accessed via
	 * EntryTypeRules::$plugin
	 *
	 * @var EntryTypeRules
	 */
	public static $plugin;

	// Public Properties
	// =========================================================================

	/**
	 * To execute your plugin’s migrations, you’ll need to increase its schema version.
	 */
	public string $schemaVersion = '1.0.0';

	/**
	 * Set to `true` if the plugin should have a settings view in the control panel.
	 */
	public bool $hasCpSettings = true;

	/**
	 * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
	 */
	public bool $hasCpSection = false;

	// Public Methods
	// =========================================================================

	/**
	 * Set our $plugin static property to this class so that it can be accessed via
	 * EntryTypeRules::$plugin
	 *
	 * Called after the plugin class is instantiated; do any one-time initialization
	 * here such as hooks and events.
	 *
	 * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
	 * you do not need to load it in your init() method.
	 */
	public function init(): void
	{
		parent::init();
		self::$plugin = $this;

		Craft::setAlias('@plugin', $this->getBasePath());

		// Let's put our own data regarding the section into the entry edit page in the CP
		Craft::$app->view->hook('cp.entries.edit.meta', function (array &$context) {
			$injectedHtml = '';
			$entry = $context['entry'];
			if ($entry !== null && $entry->section->type !== 'single') {
				// Create the elements we are going to inject
				$injectedHtml = '<div id="entryTypeRulesSectionId" data-value="' . $entry->section->id . '"></div>';
			}
			return $injectedHtml;
		});

		// Watch the template rendering to see if we are in an entry edit form
		Event::on(
			View::class,
			View::EVENT_AFTER_RENDER_TEMPLATE,
			function () {
				// Check the segments to see if we are in an entry edit form, if so register the entry bundle
				if (
					Craft::$app->getRequest()->isCpRequest &&
					Craft::$app->getRequest()->getSegment(1) === 'entries' &&
					Craft::$app->getRequest()->getSegment(3) !== ''
				) {
					// Inject our asset bundle, and start it up with some JS
					Craft::$app->getView()->registerAssetBundle(EntryTypeRulesAsset::class, View::POS_END);
					Craft::$app->getView()->registerJs('new Craft.EntryTypeRules();', View::POS_READY);
				}
			}
		);

		// Watch the slide out element editor window to see if we are editing an entry in the slide-out
		Event::on(
			Element::class,
			Element::EVENT_DEFINE_SIDEBAR_HTML,
			function (DefineHtmlEvent $event) {
				$element = $event->sender;
				// If the element is a Craft Entry
				if (is_a($element, 'craft\elements\Entry')) {
					// Get the section ID and section type the entry belongs to
					$sectionId = $element->section->id;
					// If it is not a single, inject out fields and register the slideout bundle
					if ($element->section->type !== 'single') {
						// Get the views namespace
						$viewNamespace = Craft::$app->getView()->namespace;
						// Create the elements we are going to inject (Note: the ID's will automatically be namespaced for the view by Craft)
						$injectedHtml = '<div id="entryTypeRulesSectionId" data-value="' . $sectionId . '"></div>';
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

		return Craft::$app->controller->renderTemplate(
			'entry-type-rules/settings',
			[
				'settings' => $this->getSettings(),
				'overrides' => $overrides,
			]
		);
	}


	// Protected Methods
	// =========================================================================

	/**
	 * Creates and returns the model used to store the plugin’s settings.
	 */
	protected function createSettingsModel(): ?Model
	{
		return new Settings();
	}
}
