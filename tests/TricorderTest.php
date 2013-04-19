<?php
/**
 * PHP-Tricorder
 *
 * A CLI utility that will scan a structure file created using
 * phpDocumentor and give you some suggestions on how to test
 * the classes and methods present in the structure file
 *
 * @author Chris Hartjes
 * @author Yannick Voyer
 * @version 0.1
 */

class TricorderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The output of the command
     *
     * @var string
     */
    private static $output;

    public static function setUpBeforeClass()
    {
        // We need to call this only once
        $argv = array(
            '',
            'structure.xml',
        );

        ob_start();
        include __DIR__ . "/../tricorder.php";
        self::$output = ob_get_clean();
    }

    public function testShouldOutputTheScannedClass()
    {
        $this->assertContains('Scanning ReferenceClass', self::$output);
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
            array('./TestClass.php -- \Grumpy\Dependency\Foo might need to be injected for testing purposes'),
            array('./TestClass.php -- \Grumpy\Foo might need to be injected for testing purposes due to static method call'),
        array('returnSpecificObjectType -- test method returns \Grumpy\Foo instances'), array('_protectedMethod -- non-public methods are difficult to test in isolation'),
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
        $this->assertContains($suggestion, self::$output);
    } 

    public function testShouldSuggestToTestTheProtectedAttributeForAnObjectType()
    {
        $this->markTestIncomplete('todo');
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
