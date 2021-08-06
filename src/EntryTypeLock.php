<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can include entry types based on their user group
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2021 Foster Commerce
 */

namespace fostercommerce\entrytypelock;

use fostercommerce\entrytypelock\services\EntryTypeLockService as EntryTypeLockServiceService;

use Craft;
use craft\base\Plugin;
use craft\base\Element;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\View;
use craft\events\DefineHtmlEvent;
use yii\base\Event;

use fostercommerce\entrytypelock\resources\EntryTypeLockEntryBundle;
use fostercommerce\entrytypelock\resources\EntryTypeLockSlideoutBundle;
use fostercommerce\entrytypelock\services\EntryTypeLockService;
use fostercommerce\entrytypelock\models\Settings;

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
 * @package   EntryTypeLock
 * @since     1.0.0
 *
 * @property  EntryTypeLockServiceService $entryTypeLockService
 */
class EntryTypeLock extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * EntryTypeLock::$plugin
     *
     * @var EntryTypeLock
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = false;

    protected function createSettingsModel()
    {
        return new Settings();
    }

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * EntryTypeLock::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@plugin', $this->getBasePath());

        // Let's put our own data regarding the section into the entry edit page in the CP
        Craft::$app->view->hook('cp.entries.edit.meta', function (array &$context) {
            $hiddenData = '';
            $entry = $context['entry'];
            if ($entry !== null) {
                $hiddenData .= '<div data-etl-entry-section-id data-value="' . $entry->section->id . '"></div>';
                $hiddenData .= '<div data-etl-entry-section-type data-value="' . $entry->section->type . '"></div>';
            }
            return $hiddenData;
        });

        // Watch the template rendering to see if we are in an entry edit form
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_TEMPLATE,
            function () {
                // Check the segments to see if we are in an entry edit form, if so register the entry bundle
                if (
                    Craft::$app->getRequest()->isCpRequest &&
                    Craft::$app->getRequest()->getSegment(1) == 'entries' &&
                    Craft::$app->getRequest()->getSegment(3) != ''
                ) {
                    Craft::$app->getView()->registerAssetBundle(EntryTypeLockEntryBundle::class, View::POS_END);
                    Craft::$app->getView()->registerJs('new Craft.EntryTypeLockEntry();', View::POS_READY);
                }
            }
        );

        // Watch the slide out element editor window to see if we are editing an entry in the slideout
        Event::on(
            Element::class,
            Element::EVENT_DEFINE_SIDEBAR_HTML,
            function (DefineHtmlEvent $event) {
                $element = $event->sender;
                // If the element is a Craft Entry
                if (is_a($element, 'craft\elements\Entry')) {
                    // Get the section ID and section type the entry belongs to
                    $sectionId = $element->section->id;
                    $sectionType = $element->section->type;
                    // If it is not a single, inject out fields and register the slideout bundle
                    if ($sectionType != 'single') {
                        $viewNamespace = Craft::$app->getView()->namespace;
                        $injectedHtml = '<div id="entryTypeLockSectionId" data-value="' . $sectionId . '"></div>';
                        $injectedHtml .= '<div id="entryTypeLockSectionType" data-value="' . $sectionType . '"></div>';
                        $event->html = $injectedHtml . $event->html;
                        Craft::$app->getView()->registerAssetBundle(EntryTypeLockSlideoutBundle::class, View::POS_END);
                        Craft::$app->getView()->registerJs('new Craft.EntryTypeLockSlideout("' . $viewNamespace . '");', View::POS_READY);
                    }
                }

            }
        );

        // When the plugin is installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                    // TODO: Show warnings of existing limit breaches
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
                'entry-type-lock',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
