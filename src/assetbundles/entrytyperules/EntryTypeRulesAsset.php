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

namespace fostercommerce\entrytyperules\assetbundles\entrytyperules;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * EntryTypeRulesAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
class EntryTypeRulesAsset extends AssetBundle
{
	// Public Methods
	// =========================================================================

	/**
	 * Initializes the bundle.
	 */
	public function init(): void
	{
		// define the path that your publishable resources live
		$this->sourcePath = '@fostercommerce/entrytyperules/assetbundles/entrytyperules/dist';

		// define the dependencies
		$this->depends = [
			CpAsset::class,
		];

		// define the relative path to CSS/JS files that should be registered with the page
		// when this asset bundle is registered
		$this->js = [
			'js/EntryTypeRules.js',
		];

		parent::init();
	}
}
