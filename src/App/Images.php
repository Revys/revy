<?php

namespace Revys\Revy\App;

use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
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
     * @return Image
     * @todo Add ImageAddEvent
     */
    public function add($image)
    {
        $object = $this->getObject();

        $image = Image::new($image);

        $image->setObject($object);

        $image->setUniqueName();

        $image->getInstance()->storeAs($image->getDir(), $image->filename, 'public');

        $image->save();

        $this->push($image);

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

        $this->reject(function ($item) use ($image) {
            return $item->filename == $image->filename;
        });
    }
}