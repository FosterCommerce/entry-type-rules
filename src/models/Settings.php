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

namespace fostercommerce\entrytyperules\models;

use fostercommerce\entrytyperules\EntryTypeRules;

use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * EntryTypeRules Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var array|string The section and entry type map of rules to limit entry types
     *
     * [
        'pages' => [
            'blog' => [
                'limit' => 1,
                'userGroups' => [
                    'siteAdmins'
                ]
            ]
        ]
    ]
     */
    public array|string $sections = [];

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            [['sections'], ArrayValidator::class]
        ];
    }
}
