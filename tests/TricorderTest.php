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

    public function testShouldSuggestToTestTheProtectedAttributeForAnObjectType()
    {
        $this->markTestIncomplete('todo');
    }

    public function testShouldSuggestToTestForReturnedBooleanValue()
    {
        $this->assertContains('returnBoolean -- test method returns boolean values', self::$output);
    }

    public function testShouldNotSuggestCoveredAnnotationOfBoolean()
    {
        $this->assertNotContains('returnBooleanCovered', self::$output);
    }

    public function testShouldSuggestToTestStringArgumentWithNullOrEmptyString()
    {
        $this->assertContains('acceptStringParam -- test $value using null or empty strings', self::$output);
    }

    public function testShouldSuggestToTestForVoidReturnedValue()
    {
        $this->assertContains('returnVoid -- test method returns void instances', self::$output);
    }

    public function testShouldNotOutputCoveredAnnotationForStringArgumentValue()
    {
        $this->assertNotContains('acceptStringParamCovered', self::$output);
    }

    public function testShouldNotOutputCoveredAnnotationForVoidReturnedValue()
    {
        $this->assertNotContains('returnVoidCovered', self::$output);
    }

    public function testShouldSuggestToTestIntegerArgumentWithNonIntegerValue()
    {
        $this->assertContains('acceptIntegerParam -- test $foo using non-integer values', self::$output);
    }

    public function testShouldSuggestToTestReturnedArray()
    {
        $this->assertContains('returnArray -- test method returns array instances', self::$output);
    }

    public function testShouldNotOutputCoveredMethodAcceptsIntegerValuesAndArrayValues()
    {
        $this->assertNotContains('acceptIntegerParamCovered', self::$output);
    }

    public function testShouldNotOutputCoveredMethodForReturnedArray()
    {
        $this->assertNotContains('returnArrayCovered', self::$output);
    }

    public function testShouldSuggestToTestObjectArgumentUsingMock()
    {
        $this->assertContains('acceptGrumpyFoo -- mock $foo as \Grumpy\Foo', self::$output);
    }

    public function testShouldSuggestToTestReturnedIntegerValue()
    {
        $this->assertContains('returnInteger -- test method returns non-integer values', self::$output);
    }

    public function testShouldSuggestToTestFloatArgument()
    {
        // @todo change output of message (Mock $value for float)
        $this->assertContains('acceptFloatParam -- mock $value as float', self::$output);
    }

    public function testShouldSuggestToTestReturnedValueFromMockObject()
    {
        $this->markTestIncomplete('returnValueFromObject: Suggest to test returned value from mock ->will($this->returnValue())');
    }

    public function testShouldSuggestToTestArgumentPassedToMockObject()
    {
        $this->markTestIncomplete('passArgumentToObject: Suggest to test argument passed to mock ->with($value)');
    }

    public function testShouldWarnAboutUsingDependencyInMethod()
    {
        $this->assertContains('./TestClass.php -- \Grumpy\Dependency\Foo might need to be injected for testing purposes', self::$output);
    }

    public function testShouldWarnAboutUsingStaticCallInMethods()
    {
        $this->assertContains(
            './TestClass.php -- \Grumpy\Foo might need to be injected for testing purposes due to static method call',
            self::$output
        );
    }

    public function testShouldSuggestToTestReturnInstanceType()
    {
        $this->assertContains('returnSpecificObjectType -- test method returns \Grumpy\Foo instances', self::$output);
    }

    public function testShouldWarnAboutUsingProtectedMethods()
    {
        $this->assertContains('_protectedMethod -- non-public methods are difficult to test in isolation', self::$output);
    }
}
