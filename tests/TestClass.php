<?php

/**
 * Class to be used as a reference for testing purposes
 *
 * @package php-tricorder
 * @author Chris Hartjes
 */

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
	 * Public method that accepts one parameter and returns nothing
	 *
	 * @param string $value
	 * @return void
	 */
	public function acceptParamReturnVoid($value)
	{
		// Do some work on $value if you want	
	}

	/**
	 * Public method that accepts one parameter and returns nothing
	 *
	 * @param string $value
	 * @return void
	 * @tricorder coversMethodAcceptsStringValues testMethodAcceptsStringValues
	 * @tricorder coversMethodReturnsVoidValues testMethodReturnsVoidValues
	 */
	public function acceptParamReturnVoidCovered($value)
	{
		// Do some work on $value if you want	
	}

	/**
	 * Public method that accepts one parameter and returns an array 
	 *
	 * @param integer $foo
	 * @return array 
	 */
	public function acceptParamReturnArray($foo)
	{
		return array('foo' => $foo);
	} 

	/**
	 * Public method that accepts one parameter and returns an array 
	 *
	 * @param integer $foo
	 * @return array 
	 * @tricorder coversMethodAcceptsIntegerValues testMethodAcceptsIntegerValues
	 * @tricorder coversMethodReturnsArrayValues testMethodReturnsArrayValues
	 */
	public function acceptParamReturnArrayCovered($foo)
	{
		return array('foo' => $foo);
	} 

	/**
	 * Public method that accepts a parameter of a specific type and returns
	 * an integer
	 *
	 * @param \Grumpy\Foo $foo
	 * @return integer
	 */
	public function acceptGrumpyFoo(\Grumpy\Foo $foo)
	{
		return 3;
	}

	/**
	 * Public method that instantiates a dependency inside it
	 *
	 * @param float $value
	 * @return integer
	 */
	public function createsDependency($value)
	{
		$foo = new \Grumpy\Foo();
		return $foo->fizzBuzz($value);
	}

	/**
	 * Public method that does a static method call inside
	 *
	 * @param string $value
	 * @return string
	 */
	public function usesStaticMethodCall($value)
	{
		$tmp = \Grumpy\Foo::fizzBuzz($value);
		return $tmp;
	}

	/**
	 * Public method that returns a specfic object type
	 * 
	 * @return \Grumpy\Foo 
	 */
	public function returnSpecificObjectType()
	{
		return new \Grumpy\Foo();
	}

	/**
	 * Protected method that accepts a value and returns a boolean
	 * @param integer $value
	 * @return boolean
	 */
	protected function _returnBoolean($value)
	{
		if ($value % 2 == 0) {
			return true;
		}

		return false;
	}
}