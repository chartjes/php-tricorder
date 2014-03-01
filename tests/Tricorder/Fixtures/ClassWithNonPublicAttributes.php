<?php

// Class for testing that contains both protected and private class attributes
class NonPublicAttributes
{
    protected $protectedAtt;
    private $privateAtt;

    public function publicMethod()
    {
    }

    protected function protectedMethod()
    {
    }
}
