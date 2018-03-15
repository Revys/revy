<?php

namespace Revys\Revy\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        $pathToFactories = base_path('vendor/revys/revy/src/database/factories');

        $this->app->make('Illuminate\Database\Eloquent\Factory')->load($pathToFactories);
    }

    protected function signIn($user = null)
    {
        $user = $user ?: create('App\User');

        $this->actingAs($user);

        return $this;
    }
}