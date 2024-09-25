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

namespace fostercommerce\entrytyperules\controllers;

use Craft;

use craft\elements\Entry;
use craft\web\Application;
use craft\web\Controller;
use craft\web\User;
use fostercommerce\entrytyperules\EntryTypeRules;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
class DefaultController extends Controller
{
	// Protected Properties
	// =========================================================================
	protected array|int|bool $allowAnonymous = [];

	// Public Methods
	// =========================================================================

	/**
	 * Handle a request going to our plugin's index action URL,
	 * e.g.: actions/entry-type-rules/default
	 *
	 * @throws \Throwable
	 */
	public function actionIndex(): mixed
	{
		/** @var Application $app */
		$app = Craft::$app;
		// Get the section ID from a query param we will include in the ajax request
		/** @var int $sectionId */
		$sectionId = $app->request->getQueryParam('sectionId');
		$entryId = $app->request->getQueryParam('entryId');

		$result = [
			'sectionId' => 0,
			'lockedEntryTypes' => [],
			'entryExists' => Entry::find()->id($entryId)->exists(),
		];

		if ($sectionId) {
			$result['sectionId'] = $sectionId;

			/** @var User $user */
			$user = Craft::$app->getUser();

			$result['lockedEntryTypes'] = EntryTypeRules::getInstance()
				?->entryTypeRulesService
				->getLockedEntryTypes($sectionId, $user);
		}

		return $this->asJson($result);
	}
}
