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

namespace fostercommerce\entrytyperulestests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use fostercommerce\entrytyperules\Plugin;

/**
 * ExampleUnitTest
 *
 *
 * @author    Foster Commerce
 * @package   EntryTypeRules
 * @since     1.0.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            Plugin::class,
            Plugin::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
