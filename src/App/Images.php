<?php

namespace Revys\Revy\App;

use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Revys\Revy\App\Events\ImageAdded;
use Revys\Revy\App\Traits\WithImages;

class Images extends Collection
{
    protected $object;

    /**
     * @return null|WithImages|Entity
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Entity|WithImages $object
     * @return self
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param UploadedFile $image
     * @param bool $createThumbnails
     * @return Image
     */
    public function add($image, $createThumbnails = true)
    {
        $object = $this->getObject();

        $image = Image::new($image);

        $image->setObject($object);

        $image->setUniqueName();

        if ($object->getImageThumbnail('original'))
            $image->createThumbnail('original');
        else
            $image->getInstance()->storeAs($image->getDir(), $image->filename, 'public');

        $image->save();

        $this->push($image);

        if ($createThumbnails) {
             $image->createThumbnails();
        }

        event(new ImageAdded($image));

        return $image;
    }

    /**
     * @param array $images
     * @return array
     */
    public function addMany($images)
    {
        $result = [];

        foreach ($images as $image) {
            $result[] = $this->add($image);
        }

        return $result;
    }

    /**
     * @param $image
     * @return Image
     */
    public function set($image)
    {
        $this->removeAll();

        return $this->add($image);
    }

    public function removeAll()
    {
        $object = $this->getObject();

        Storage::disk('public')->deleteDirectory($object->getImagesDir());

        try {
            Image::where([
                'object_id' => $object->id,
                'model'     => $object->getModelName()
            ])->delete();
        } catch (\Exception $e) {
            return;
        }

        $this->each(function ($image, $key) {
            $this->forget($key);
        });
    }

    public function remove($image)
    {
        $object = $this->getObject();

        if (! ($image instanceof Image)) {
            $image = $object->getImage($image);
        }

        Storage::disk('public')->delete($image->getPath());

        try {
            Image::where([
                'object_id' => $object->id,
                'model'     => $object->getModelName(),
                'filename'  => $image->filename
            ])->delete();
        } catch (\Exception $e) {
            return;
        }

        // Remove thumbnails
        $image->removeThumbnails();

        $this->reject(function ($item) use ($image) {
            return $item->filename == $image->filename;
        });
    }

    /**
     * @param string $name
     */
    public function removeThumbnail($name)
    {
        $this->assertThumbnailExists($name);

        Storage::disk('public')->deleteDirectory($this->getObject()->getImageDir($name));
    }

    public function removeThumbnails()
    {
        $object = $this->getObject();

        foreach ($object->getImageThumbnails() as $name => $modifier) {
            Storage::disk('public')->deleteDirectory($object->getImageDir($name));
        }
    }

    /**
     * @param string $name
     */
    public function recreateThumbnail($name)
    {
        $this->assertThumbnailExists($name);

        $this->each(function($image) use ($name) {
            $image->recreateThumbnail($name);
        });
    }

    public function recreateThumbnails()
    {
        foreach ($this->getObject()->getImageThumbnails() as $name => $modifier) {
            $this->each(function ($image) use ($name) {
                $image->recreateThumbnail($name);
            });
        }
    }

    /**
     * @param $name
     * @throws \Exception
     */
    public function assertThumbnailExists($name) : void
    {
        if (! $this->getObject()->imageThumbnailExists($name)) {
            throw new \Exception(
                'Thumbnail with name "' . $name . '" does not exists at model ' . $this->object->getMorphClass()
            );
        }
    }
}