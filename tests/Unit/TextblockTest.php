<?php

namespace Revys\Revy\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Textblock;
use Revys\Revy\Tests\TestCase;

class TextblockTest extends TestCase
{
    use DatabaseMigrations;
    
    public function test_getText()
    {
        $textblock = Textblock::create([
            'title' => 'Title',
            'text' => 'Text',
            Entity::getStringIdField() => 'textblock_1'
        ]);

        $this->assertEquals($textblock->text, Textblock::getText('textblock_1'));

        $this->assertEquals('', Textblock::getText('textblock_2'));
    }
}
