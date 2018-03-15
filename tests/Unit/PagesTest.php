<?php

namespace Revys\Revy\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Revys\Revy\App\Page;
use Revys\Revy\App\Revy;
use Revys\Revy\App\RevyBase;
use Revys\Revy\Tests\TestCase;

class PagesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \Revys\Revy\App\Page
     */
    private $page;

    public function setUp()
    {
        parent::setUp();

        $this->page = create("Revys\Revy\App\Page");
    }

    public function test_page_has_path()
    {
        $path = $this->page->getPath();

        $this->assertEquals('/' . Revy::getLocale() . '/' . $this->page->getUrlID(), $path);
    }
}
