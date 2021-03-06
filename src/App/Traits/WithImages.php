<?php

namespace Revys\Revy\App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Intervention\Image\Exception\NotFoundException;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Image;
use Revys\Revy\App\Images;

trait WithImages
{
    protected $images;

    /**
     * @return Images
     */
    public function images()
    {
        if ($this->images !== null)
            return $this->images;

        $this->images = (new Images(
            Image::where([
                'object_id' => $this->id,
                'model'     => $this->getModelName()
            ])->get()->map(function ($image) {
                return $image->setObject($this);
            })
        ))->setObject($this);

        return $this->images;
    }

    public function hasImages()
    {
        return $this->images()->isNotEmpty();
    }

    /**
     * @return array
     */
    public function getImageThumbnails()
    {
        return [];
    }

    /**
     * @param string $type
     * @return \Closure|bool
     */
    public function getImageThumbnail($type)
    {
        return $this->getImageThumbnails()[$type] ?? false;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function imageThumbnailExists($type)
    {
        return isset($this->getImageThumbnails()[$type]);
    }

    public function getImagesDir()
    {
        return 'images/' . $this->getModelName() . '/' . $this->id;
    }

    /**
     * @param string $image
     * @param string $type
     * @return string
     */
    public function getImagePath($image, $type = 'original')
    {
        return $this->getImageDir($type) . '/' . $image;
    }

    public function getImageDir($type = 'original')
    {
        return $this->getImagesDir() . '/' . $type;
    }

    /**
     * @param string $imageName
     * @return Image
     * @throws NotFoundException
     */
    public function getImage($imageName)
    {
        $image = Image::where([
            'object_id' => $this->id,
            'model'     => $this->getModelName(),
            'filename'  => $imageName
        ])->first();

        if (! $image)
            throw new NotFoundException('Image could not be found [' . $imageName . ']');

        $image->setObject($this);

        return $image;
    }

    public function image()
    {
        return optional($this->images()->first());
    }
}