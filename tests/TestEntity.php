<?php

namespace Revys\Revy\Tests;

use Illuminate\Support\Facades\Storage;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Image;
use Revys\Revy\App\Traits\WithImages;

class TestEntity extends Entity
{
    use WithImages;

    /**
     * @return array
     */
    public function getImageThumbnails()
    {
        return [
            'original' => function (Image $image, Entity $object) {
                return $image;
            },
            'test'     => function (Image $image, Entity $object) {
                return \Image::make(Storage::disk('public')->get($image->getPath()))->resize(100, 100);
            }
        ];
    }
}