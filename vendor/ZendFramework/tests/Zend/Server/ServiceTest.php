<?php

namespace ZendTest\Server;

use PHPUnit_Framework_TestCase as TestCase,
    Zend\Server\Service,
    Zend\Stdlib\CallbackHandler;

class ServiceTest extends TestCase
{
    public function testServiceRequiresANameAndTarget()
    {
        $service = new Service('foo');
    }
}
