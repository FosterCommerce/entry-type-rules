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

use fostercommerce\entrytypelock\EntryTypeRules;

use Craft;
use craft\web\Controller;

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

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/entry-type-rules/default
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = [
            'sectionId' => 0,
            'lockedEntryTypes' => []
        ];

        // Get the section ID from a query param we will include in the ajax request
        $sectionId = Craft::$app->request->getQueryParam('sectionId');

        if ($sectionId) {
            $result['sectionId'] = $sectionId;
            $result['lockedEntryTypes'] = EntryTypeRules::$plugin->entryTypeRulesService->getLockedEntryTypes($sectionId);
            return json_encode($result);
        } else {
            return $result;
        }
    }
}
