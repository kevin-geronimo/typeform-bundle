<?php

namespace TypeformBundle\Tests\Functional;

use Nyholm\BundleTest\BaseBundleTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;


class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return FrameworkBundle::class;
    }
    public function testRegisterBundle()
    {
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertTrue($container->has('kernel'));
    }
}