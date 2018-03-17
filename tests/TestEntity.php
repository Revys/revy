<?php

namespace Revys\Revy\Tests;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Revys\Revy\App\Entity;
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
                return $image->resize(20, 20);
            },
            'test'     => function (Image $image, Entity $object) {
                return $image->resize(100, 100);
            },
            'test2'    => function (Image $image, Entity $object) {
                return $image->resize(15, 15);
            }
        ];
    }
}