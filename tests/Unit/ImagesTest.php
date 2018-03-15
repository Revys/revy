<?php

namespace Revys\Revy\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Revys\Revy\App\Image;
use Revys\Revy\Tests\TestCase;

class ImagesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @param Image $image
     */
    public function assertImageExists(Image $image)
    {
        Storage::disk('public')->assertExists($image->getPath());

        $this->assertDatabaseHas('images', [
            'object_id' => $image->getObject()->id,
            'model'     => $image->getObject()->getModelName(),
            'filename'  => $image->filename
        ]);
    }

    /**
     * @param Image $image
     */
    private function assertImageMissing($image)
    {
        Storage::disk('public')->assertMissing($image->getPath());

        $this->assertDatabaseMissing('images', [
            'object_id' => $image->getObject()->id,
            'model'     => $image->getObject()->getModelName(),
            'filename'  => $image->filename
        ]);
    }

    /**
     * @param string $name
     * @return \Illuminate\Http\Testing\File
     */
    public static function createImage($name = null)
    {
        $name = $name ?: str_random(10);

        return UploadedFile::fake()->image($name);
    }

    public function setUp()
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_image_can_be_added()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();

        $image = $object->images()->add($image);

        $this->assertImageExists($image);
    }

    public function test_images_can_be_added()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();
        $image2 = self::createImage();

        list($image, $image2) = $object->images()->addMany([
            $image,
            $image2
        ]);

        $this->assertImageExists($image);
        $this->assertImageExists($image2);
    }

    public function test_image_can_be_set()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();

        $image = $object->images()->add($image);

        $this->assertImageExists($image);

        $image2 = self::createImage();

        $image2 = $object->images()->set($image2);

        $this->assertImageMissing($image);
        $this->assertImageExists($image2);
    }

    /** @test */
    public function image_can_get_unique_name()
    {
        $object = WithImagesTraitTest::getObject();
        $image = self::createImage('image.png');
        $image = $object->images()->add($image);

        $this->assertEquals(
            'image_2.png',
            Image::getUniqueName($image->filename, $object->getModelName(), $object->id)
        );
    }

    /** @test */
    public function images_with_equival_names_can_be_added()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage('image.png');
        $image2 = self::createImage('image.png');

        $image = $object->images()->add($image);
        $image2 = $object->images()->add($image2);

        $this->assertImageExists($image);
        $this->assertImageExists($image2);
    }

    public function test_image_can_be_removed_by_Image_object()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();

        $image = $object->images()->add($image);

        $this->assertImageExists($image);

        $object->images()->remove($image);

        $this->assertImageMissing($image);
    }

    public function test_image_can_be_removed_by_image_filename()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();

        $image = $object->images()->add($image);

        $this->assertImageExists($image);

        $object->images()->remove($image->filename);

        $this->assertImageMissing($image);
    }
}