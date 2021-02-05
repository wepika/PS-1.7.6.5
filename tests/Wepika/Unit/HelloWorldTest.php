<?php

// Namespace sera en fonction de votre arborescence
namespace Tests\Wepika\Unit;

// Attention à ne pas utiliser le TestCase de React
use PHPUnit\Framework\TestCase;

class HelloWorldTest extends TestCase
{
    public function testVariableEqualsHelloWorld()
    {
        $variable = 'Hello World !';

        $this->assertEquals('Hello World !', $variable);
    }

    public function testVariableEqualsGoodbyeWorld()
    {
        $variable = 'Hello World !';

        $this->assertEquals('Goodbye World !', $variable);
    }
}