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

namespace fostercommerce\entrytypelock\controllers;

use fostercommerce\entrytypelock\services\EntryTypeLockService;

use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class SettingsController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    /**
     * Handle a request going to our plugin's action URL for saving settings,
     * e.g.: actions/craft-entry-type-lock/save-settings
     *
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws MissingComponentException
     */
    public function actionSaveSettings()
    {
        // Require posts to this controller action only
        $this->requirePostRequest();

        // Get the plugin instance to ensure it is installed
        $pluginHandle = Craft::$app->getRequest()->getRequiredBodyParam('pluginHandle');
        $plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);
        if ($plugin === null) {
            throw new NotFoundHttpException('Plugin not found');
        }

        // Get the posted form values from the settings page submission and send them to the service to be formatted
        $request = Craft::$app->getRequest();
        $formParams = $request->getBodyParams();
        $settings['sections'] = EntryTypeLockService::instance()->formatSectionsSettings($formParams);

        // Save the settings, and if they fail, display an error
        if (!Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)) {
            Craft::$app->getSession()->setError(Craft::t('app', "Couldn’t save plugin settings."));

            // Send the plugin back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin,
            ]);

            return null;
        }

        // If everything went well, display a confirmation message and send the user back to the same settings page
        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
        return $this->redirectToPostedUrl();
    }
}