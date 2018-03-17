<?php

namespace Revys\Revy\Tests\Unit;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Revys\Revy\App\Events\ImageAdded;
use Revys\Revy\App\Image;
use Revys\Revy\App\Images;
use Revys\Revy\Tests\TestCase;

class ImagesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var FilesystemAdapter
     */
    public $disk;

    public function setUp()
    {
        parent::setUp();

        Storage::fake('public');

        $this->disk = Storage::disk('public');
    }

    /**
     * @param Image $image
     */
    public function assertImageExists(Image $image)
    {
        $this->disk->assertExists($image->getPath());

        $this->assertDatabaseHas('images', [
            'object_id' => $image->getObject()->id,
            'model'     => $image->getObject()->getModelName(),
            'filename'  => $image->filename
        ]);
    }

    /**
     * @param bool $createThumbnails
     * @return array
     */
    public static function createAttachedImage($createThumbnails = false)
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();

        $image = $object->images()->add($image, $createThumbnails);

        return [$object, $image];
    }

    /**
     * @param Image $image
     */
    private function assertImageMissing($image)
    {
        $this->disk->assertMissing($image->getPath());

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

    public function test_image_can_be_added()
    {
        [$object, $image] = self::createAttachedImage();

        $this->assertImageExists($image);
    }

    public function test_images_can_be_added()
    {
        $object = WithImagesTraitTest::getObject();

        $image = self::createImage();
        $image2 = self::createImage();

        [$image, $image2] = $object->images()->addMany([
            $image,
            $image2
        ]);

        $this->assertImageExists($image);
        $this->assertImageExists($image2);
    }

    public function test_image_can_be_set()
    {
        [$object, $image] = self::createAttachedImage();

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
        [$object, $image] = self::createAttachedImage();

        $this->assertImageExists($image);

        $object->images()->remove($image);

        $this->assertImageMissing($image);
    }

    public function test_image_can_be_removed_by_image_filename()
    {
        [$object, $image] = self::createAttachedImage();

        $this->assertImageExists($image);

        $object->images()->remove($image->filename);

        $this->assertImageMissing($image);
    }

    /** @test */
    public function create_thumbnail()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnail('test');

        $this->disk->assertExists($image->getPath('test'));
    }

    /** @test */
    public function create_thumbnail_from_closure()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnail('test', function ($image, $object) {
            return $image->resize(30, 30);
        });

        $this->disk->assertExists($image->getPath('test'));

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(30, $width);
        $this->assertEquals(30, $height);
    }

    /** @test */
    public function can_not_create_thumbnail_with_not_existing_name()
    {
        [$object, $image] = self::createAttachedImage();

        $this->expectException(\Exception::class);

        $image->createThumbnail('not-existing-name');
    }

    /** @test */
    public function can_not_create_thumbnail_with_not_existing_name_from_closure()
    {
        [$object, $image] = self::createAttachedImage();

        $this->expectException(\Exception::class);

        $image->createThumbnail('not-existing-name', function ($image, $object) {
            return $image;
        });
    }

    /** @test */
    public function adding_an_image_goes_through_original_thumbnail()
    {
        // Width and height of that image is 10px
        // TestEntity has image type "original", that resize image to 20x20px
        [$object, $image] = self::createAttachedImage(true);

        [$width, $height] = getimagesize($this->disk->path($image->getPath()));

        $this->assertEquals(20, $width);
        $this->assertEquals(20, $height);
    }

    /** @test */
    public function createThumbnails_can_create_all_thumbnails()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnails();

        // Original thumbnail
        [$width, $height] = getimagesize($this->disk->path($image->getPath()));
        $this->assertEquals(20, $width);
        $this->assertEquals(20, $height);

        // Test thumbnail
        $this->disk->assertExists($image->getPath('test'));
    }

    /** @test */
    public function when_adding_image_also_creates_thumbnails()
    {
        [$object, $image] = self::createAttachedImage(true);

        $this->disk->assertExists($image->getPath('test'));
    }

    /** @test */
    public function can_remove_thumbnail()
    {
        [$object, $image] = self::createAttachedImage(true);

        $image->removeThumbnail('test');

        $this->disk->assertMissing($image->getPath('test'));
    }

    /** @test */
    public function can_not_remove_not_existing_thumbnail()
    {
        [$object, $image] = self::createAttachedImage(true);

        $this->expectException(\Exception::class);

        $image->removeThumbnail('not-exists');
    }

    /** @test */
    public function can_remove_thumbnail_of_entity()
    {
        [$object, $image] = self::createAttachedImage(true);

        $object->images()->removeThumbnail('test');

        $this->disk->assertMissing($image->getPath('test'));
    }

    /** @test */
    public function can_remove_all_thumbnails_of_entity()
    {
        [$object, $image] = self::createAttachedImage(true);

        $object->images()->removeThumbnails();

        $this->disk->assertMissing($image->getPath('test'));
        $this->disk->assertMissing($image->getPath('test2'));
    }

    /** @test */
    public function when_removing_all_entity_images_also_removes_thumbnails()
    {
        [$object, $image] = self::createAttachedImage(true);

        $object->images()->removeAll($image);

        $this->disk->assertMissing($image->getPath('test'));
    }

    /** @test */
    public function when_removing_image_also_removes_thumbnails()
    {
        [$object, $image] = self::createAttachedImage(true);

        $object->images()->remove($image);

        $this->disk->assertMissing($image->getPath('test'));
    }

    /** @test */
    public function recreate_thumbnail_of_image()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnail('test', function ($image, $object) {
            return $image->resize(30, 30);
        });

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(30, $width);
        $this->assertEquals(30, $height);

        $image->recreateThumbnail('test');

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(100, $width);
        $this->assertEquals(100, $height);

        // Can't recreate not existing thumbnail
        $this->expectException(\Exception::class);
        $image->recreateThumbnail('not-existing-thumbnail');
    }

    /** @test */
    public function can_not_recreate_not_existing_thumbnail()
    {
        [$object, $image] = self::createAttachedImage(true);

        $this->expectException(\Exception::class);

        $image->removeThumbnail('not-existing');
    }

    /** @test */
    public function recreate_thumbnail_of_entity()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnail('test', function ($image, $object) {
            return $image->resize(30, 30);
        });

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(30, $width);
        $this->assertEquals(30, $height);

        $object->images()->recreateThumbnail('test');

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(100, $width);
        $this->assertEquals(100, $height);
    }

    /** @test */
    public function recreate_all_thumbnails_of_entity()
    {
        [$object, $image] = self::createAttachedImage();

        $image->createThumbnail('test', function ($image, $object) {
            return $image->resize(30, 30);
        });
        $image->createThumbnail('test2', function ($image, $object) {
            return $image->resize(30, 30);
        });

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test')));

        $this->assertEquals(30, $width);
        $this->assertEquals(30, $height);

        $object->images()->recreateThumbnails();

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test'))); // 100x100px

        $this->assertEquals(100, $width);
        $this->assertEquals(100, $height);

        [$width, $height] = getimagesize($this->disk->path($image->getPath('test2'))); // 15x15px

        $this->assertEquals(15, $width);
        $this->assertEquals(15, $height);
    }
    
    /** @test */
    public function event_is_dispated_after_adding_an_image()
    {
        \Event::fake();

        [$object, $image] = self::createAttachedImage();

        \Event::assertDispatched(ImageAdded::class, function ($e) use ($image) {
            return $e->image->id === $image->id;
        });
    }
}