<?php

/**
 * Class to be used as a reference for testing purposes
 *
 * @package php-tricorder
 * @author Chris Hartjes
 */

namespace Tricorder\Fixtures;

/**
 * ReferenceClass
 */
class ReferenceClass
{
    /**
     * Sample protected DB attribute
     * @type \PDO
     */
    protected $_db;

    /**
     * Public method that accepts no parameter but returns boolean
     *
     * @return boolean
     */
    public function returnBoolean()
    {
        return true;
    }

    /**
     * Public method that accepts no parameter but returns boolean
     *
     * @return boolean
     * @tricorder coversMethodReturnsBooleanValues testMethodReturnsBooleanValues
     */
    public function returnBooleanCovered()
    {
        return true;
    }

    /**
     * Public method that accepts one parameter
     *
     * @param string $value
     */
    public function acceptStringParam($value)
    {
        // Do some work on $value if you want
    }

    /**
     * Public method returns nothing
     *
     * @return void
     */
    public function returnVoid()
    {
    }

    /**
     * Public method that accepts one string parameter
     *
     * @param string $value
     * @tricorder coversMethodAcceptsStringValues testMethodAcceptsStringValues
     */
    public function acceptStringParamCovered($value)
    {
        // Do some work on $value if you want
    }

    /**
     * Public method returns nothing
     *
     * @return void
     * @tricorder coversMethodReturnsVoidValues testMethodReturnsVoidValues
     */
    public function returnVoidCovered()
    {
    }

    /**
     * Public method that accepts integer
     *
     * @param integer $foo
     */
    public function acceptIntegerParam($foo)
    {
    }

    /**
     * Public method returns an array
     *
     * @return array
     */
    public function returnArray()
    {
        return array('foo' => 0);
    }

    /**
     * Public method that accepts one integer parameter
     *
     * @param integer $foo
     * @tricorder coversMethodAcceptsIntegerValues testMethodAcceptsIntegerValues
     */
    public function acceptIntegerParamCovered($foo)
    {
    }

    /**
     * Public method that returns an array
     *
     * @return array
     * @tricorder coversMethodReturnsArrayValues testMethodReturnsArrayValues
     */
    public function returnArrayCovered()
    {
        return array('foo' => 0);
    }

    /**
     * Public method that accepts a parameter of a specific type
     *
     * @param \Grumpy\Foo $foo
     */
    public function acceptGrumpyFoo(\Grumpy\Foo $foo)
    {
    }

    /**
     * Public method that returns an integer
     *
     * @return integer
     */
    public function returnInteger()
    {
        return 3;
    }

    /**
     * Public method that accept float value
     *
     * @param float $value
     */
    public function acceptFloatParam($value)
    {
    }

    /**
     * Public method that instantiates a dependency inside it
     */
    public function createsDependency()
    {
        new \Grumpy\Dependency\Foo();
    }

    /**
     * Public method that depends on a method call with a value
     *
     * @todo
     * @param Grumpy\Argument\Foo $foo
     */
    public function passArgumentToObject(\Grumpy\Argument\Foo $foo)
    {
        $foo->fizzBuzz(0);
    }

    /**
     * Public method that returns the value from an object
     *
     * @todo
     * @param Grumpy\Returns\Foo $foo
     *
     * @return integer
     */
    public function returnValueFromObject(\Grumpy\Returns\Foo $foo)
    {
        return $foo->fizzBuzz();
    }

    /**
     * Public method that does a static method call inside
     */
    public function usesStaticMethodCall()
    {
        \Grumpy\Foo::fizzBuzz();
    }

    /**
     * Public method that returns a specific object type
     *
     * @return \Grumpy\Foo
     */
    public function returnSpecificObjectType()
    {
        return new \Grumpy\Foo();
    }

    /**
     * Protected method are hard to test
     */
    protected function _protectedMethod()
    {
    }
}
