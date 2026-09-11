<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Las pruebas no dependen de los assets compilados con Vite (public/build no se versiona).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
