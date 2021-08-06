<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can include entry types based on their user group
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2021 Foster Commerce
 */

namespace fostercommerce\entrytypelock\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;


class EntryTypeLockSlideoutBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@plugin/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/EntryTypeLockSlideout.js',
        ];

        parent::init();
    }
}