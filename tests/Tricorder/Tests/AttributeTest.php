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
        $attributeParser = new \Tricorder\Scanner\AttributeScanner(FIXTURE_DIR . 'ReferenceClass.php');
        $response = $attributeParser->scan();
        $message = '$_db -- protected attributes can only be read, not altered or set';

        $this->assertContains($message, $response);
    }

    public function testFindsPrivateAttributes()
    {
        $attributeParser = new \Tricorder\Scanner\AttributeScanner(FIXTURE_DIR . 'ClassWithPrivateAttribute.php');
        $response = $attributeParser->scan();
        $message = '$_foo -- private attributes can only be read, not altered or set';

        $this->assertContains($message, $response);
    }

    public function testFindsBothPrivateAndProtectedAttributes()
    {
        $attributeParser = new \Tricorder\Scanner\AttributeScanner(FIXTURE_DIR . 'ClassWithNonPublicAttributes.php');
        $response = $attributeParser->scan();

        $suffix = ' attributes can only be read, not altered or set';
        $privateMessage = '$privateAtt -- private' . $suffix;
        $protectedMessage = '$protectedAtt -- protected' . $suffix;

        $this->assertContains($privateMessage, $response);
        $this->assertContains($protectedMessage, $response);
    }
}
