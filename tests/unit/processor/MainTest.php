<?php
/**
 * MainTest.php
 */

namespace unit\processor;
use metalguardian\fileProcessor\helpers\FPM;
use unit\TestCase;
use Yii;

/**
 * Class MainTest
 */
class MainTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $config = $this->loadConfig();
        $this->mockWebApplication($config);
    }


    public function testMain()
    {
        $module = FPM::m();

        $this->assertInstanceOf('\metalguardian\fileProcessor\Module', $module);
    }
}
