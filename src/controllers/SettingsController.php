<?php

namespace fostercommerce\entrytyperules\controllers;

use Craft;

use craft\errors\MissingComponentException;
use craft\web\Controller;
use craft\web\Request;
use fostercommerce\entrytyperules\models\Settings;
use fostercommerce\entrytyperules\Plugin;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
	protected array|int|bool $allowAnonymous = [];

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

		/** @var Plugin $plugin */
		$plugin = Plugin::getInstance();

		$settings = new Settings([
			'sections' => $request->getBodyParam('sections'),
		]);

		if (! $settings->validate() || ! Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray())) {
			Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));
		} else {
			Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
		}

		return $this->redirectToPostedUrl();
	}
}
