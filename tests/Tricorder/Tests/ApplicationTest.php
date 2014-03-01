<?php
/**
 * PHP-Tricorder
 *
 * A CLI utility that will scan a structure file created using
 * phpDocumentor and give you some suggestions on how to test
 * the classes and methods present in the structure file
 *
 * @author Chris Hartjes
 * @version 0.1
 */

namespace Tricorder\Tests;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Tricorder\Command\TricorderCommand;

/**
 * Class ApplicationTest
 *
 * @author  Yannick Voyer (http://github.com/yvoyer)
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The output of the command
     *
     * @var string
     */
    private $output;

    public function setUp()
    {
        $application = new Application();
        $application->add(new TricorderCommand());
        $command = $application->find('tricorder');

        // We need to call this only once
        $argv = array(
            'command' => $command->getName(),
            '--path'  => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Fixtures',
            '--phpdox'    => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'structure.xml',
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute($argv);
        $this->output = $commandTester->getDisplay();
    }

    public function testShouldOutputTheScannedClass()
    {
        $this->assertContains('Scanning ReferenceClass', $this->output);
    }

    /**
     * Data provider for verifying that specific suggestions appear
     * in the output
     */
    public function suggestionsDataProvider()
    {
        return array(
            array('returnBoolean -- test method returns boolean values'),
            array('acceptStringParam -- test $value using null or empty strings'),
            array('returnVoid -- test method returns void instances'),
            array('acceptIntegerParam -- test $foo using non-integer values'),
            array('returnArray -- test method returns array instances'),
            array('acceptGrumpyFoo -- mock $foo as \Grumpy\Foo'),
            array('returnInteger -- test method returns non-integer values'),
            array('acceptFloatParam -- mock $value as float'),
            array('/Fixtures/ReferenceClass.php -- \Grumpy\Dependency\Foo might need to be injected for testing purposes'),
            array('/Fixtures/ReferenceClass.php -- \Grumpy\Foo might need to be injected for testing purposes due to static method call'),
            array('returnSpecificObjectType -- test method returns \Grumpy\Foo instances'),
            array('_protectedMethod -- non-public methods are difficult to test in isolation'),
        );
    }

    /**
     * Make sure that specific suggestions are generated based on our reference
     * class
     *
     * @test
     * @dataProvider suggestionsDataProvider
     */
    public function checkForSpecificSuggestions($suggestion)
    {
        $this->assertContains($suggestion, $this->output);
    }

    public function testShouldSuggestToTestReturnedValueFromMockObject()
    {
        $this->markTestIncomplete('returnValueFromObject: Suggest to test returned value from mock ->will($this->returnValue())');
    }

    public function testShouldSuggestToTestArgumentPassedToMockObject()
    {
        $this->markTestIncomplete('passArgumentToObject: Suggest to test argument passed to mock ->with($value)');
    }

}
