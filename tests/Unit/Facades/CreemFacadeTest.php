<?php

namespace Romansh\LaravelCreem\Tests\Unit\Facades;

use Orchestra\Testbench\TestCase;
use Romansh\LaravelCreem\Facades\Creem as CreemFacade;

class CreemFacadeTest extends TestCase
{
    public function test_get_facade_accessor_returns_creem()
    {
        $ref = new \ReflectionMethod(CreemFacade::class, 'getFacadeAccessor');
        $ref->setAccessible(true);

        $value = $ref->invoke(null);

        $this->assertEquals('creem', $value);
    }
}
