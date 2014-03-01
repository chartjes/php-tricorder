<?php
/**
 * PHP-Tricorder
 *
 * A CLI utility that will scan a structure file created using
 * phpDocumentor or a PHP source file and make suggestions about
 * what to unit test or potential refactorins
 *
 * @author Chris Hartjes
 * @version 0.1
 */

namespace Tricorder\Tests;


class AttritubeTest extends \PHPUnit_Framework_TestCase
{
    public function testFindsProtectedAttributes()
    {
        $source = file_get_contents(FIXTURE_DIR . 'ReferenceClass.php');
        $attributeParser = new \Tricorder\Scanner\AttributeScanner($source);
        $response = $attributeParser->scan();
        $message = '$_db -- protected attributes can only be read, not altered or set';

        $this->assertContains($message, $response);
    }

    public function testFindsPrivateAttributes()
    {
        $source = file_get_contents(FIXTURE_DIR . 'ClassWithPrivateAttribute.php');
        $attributeParser = new \Tricorder\Scanner\AttributeScanner($source);
        $response = $attributeParser->scan();
        $message = '$_foo -- private attributes can only be read, not altered or set';

        $this->assertContains($message, $response);
    }

    public function testFindsBothPrivateAndProtectedAttributes()
    {
        $source = file_get_contents(FIXTURE_DIR . 'ClassWithNonPublicAttributes.php');
        $attributeParser = new \Tricorder\Scanner\AttributeScanner($source);
        $response = $attributeParser->scan();
        $this->assertTrue(count($response) == 2);

        $suffix = ' attributes can only be read, not altered or set';
        $privateMessage = '$privateAtt -- private' . $suffix;
        $protectedMessage = '$protectedAtt -- protected' . $suffix;

        $this->assertContains($privateMessage, $response);
        $this->assertContains($protectedMessage, $response);
    }

}
