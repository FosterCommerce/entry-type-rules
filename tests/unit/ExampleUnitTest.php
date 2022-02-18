<?php
/**
 * Entry Type Lock plugin for Craft CMS 3.x
 *
 * A Craft plugin that allows you to lock down the number of entry types in a Craft section and/or limit who can
include entry types based on their user group.
 *
 * @link      https://fostercommerce.com
 * @copyright Copyright (c) 2022 Foster Commerce
 */

namespace fostercommerce\entrytypelocktests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use fostercommerce\entrytypelock\EntryTypeLock;

/**
 * ExampleUnitTest
 *
 *
 * @author    Foster Commerce
 * @package   EntryTypeLock
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
            EntryTypeLock::class,
            EntryTypeLock::$plugin
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
