<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprigtests\unit;

use Codeception\Test\Unit;
use Craft;
use putyourlightson\sprig\variables\SprigVariable;
use UnitTester;

/**
 * @author    PutYourLightsOn
 * @package   Sprig
 * @since     1.0.0
 */

class SprigVariableTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var SprigVariable
     */
    protected $variable;

    protected function _before()
    {
        parent::_before();

        $this->variable = new SprigVariable();
    }

    public function testHtmxScriptExistsLocally()
    {
        // Simplified check that file version exists locally
        $version = $this->variable->htmxVersion;
        $filepath = '@putyourlightson/sprig/resources/js/htmx-'.$version.'.js';

        $this->assertFileExists(Craft::getAlias($filepath));
    }

    public function testHyperscriptScriptExistsLocally()
    {
        // Simplified check that file version exists locally
        $version = $this->variable->hyperscriptVersion;
        $filepath = '@putyourlightson/sprig/resources/js/hyperscript-'.$version.'.js';

        $this->assertFileExists(Craft::getAlias($filepath));
    }

    public function testHtmxScriptExistsRemotely()
    {
        Craft::$app->getConfig()->env = 'production';

        $this->_testScriptExistsRemotely($this->variable->getScript());
    }

    public function testHyperscriptScriptExistsRemotely()
    {
        Craft::$app->getConfig()->env = 'production';

        $this->_testScriptExistsRemotely($this->variable->getHyperscript());
    }

    public function testValsIsJsonEncoded()
    {
        $vals = $this->variable->vals(['a' => 123, 'b' => 'abc']);

        $this->assertEquals('s-vals=\'{"a":123,"b":"abc"}\'', $vals);
    }

    public function testValsIsJsonEncodedAndSanitized()
    {
        $vals = $this->variable->vals(['x' => '"alert(\'xss\')']);

        $this->assertEquals('s-vals=\'{"x":"\u0022alert(\u0027xss\u0027)"}\'', $vals);
    }

    private function _testScriptExistsRemotely(string $script)
    {
        $client = Craft::createGuzzleClient();

        preg_match('/src="(.*?)"/', (string)$script, $matches);
        $url = $matches[1];

        $statusCode = $client->get($url)->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }
}
