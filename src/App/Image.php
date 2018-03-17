<?php

namespace Revys\Revy\App;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Revys\Revy\App\Traits\WithImages;

/**
 * @property int object_id
 * @property string filename
 * @property string model
 * @property string hash
 */
class Image extends Entity
{
    /**
     * @var null|WithImages
     */
    protected $object;
    /**
     * @var UploadedFile|null
     */
    protected $instance;
    protected $appends = ['path'];

    public function getPathAttribute()
    {
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function setUniqueName()
    {
        $object = $this->getObject();

        return $this->filename = $this->getUniqueName($this->filename, $object->getModelName(), $object->id);
    }

    /**
     * @param string $filename
     * @param string $model
     * @param int $object_id
     * @return string
     */
    public static function getUniqueName($filename, $model, $object_id)
    {
        $pathinfo = pathinfo($filename);
        $originalName = $filename = $pathinfo['filename'];

        $n = 2;
        while (Image::where([
            'object_id' => $object_id,
            'model'     => $model,
            'filename'  => $filename . (isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '')
        ])->exists()) {
            $filename = $originalName . '_' . $n++;
        }

        return $filename . (isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '');
    }

    /**
     * @return UploadedFile|null
     */
    public function getInstance()
    {
        return $this->instance;
    }

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
        $this->object_id = $object->id;
        $this->model = $object->getModelName();

        return $this;
    }

    /**
     * @param UploadedFile $file
     * @param null|string $filename
     * @return Image
     */
    public static function new($file, $filename = null)
    {
        $image = new Image();

        $image->instance = $file;
        $image->filename = $filename ?: $file->getClientOriginalName();

        return $image;
    }

    public function getDir($type = 'original')
    {
        return $this->getObject()->getImageDir($type);
    }

    public function getPath($type = 'original')
    {
        return $this->getDir($type) . '/' . $this->filename;
    }

    public function remove()
    {
        return $this->getObject()->images()->remove($this);
    }

    /**
     * @param $name
     * @param null|\Closure $modifier
     * @return bool|\Intervention\Image\Image
     * @todo Create thumbnails on ImageAddEvent
     * @throws \Exception
     */
    public function createThumbnail($name, $modifier = null)
    {
        $this->assertThumbnailExists($name);

        $function = $this->object->getImageThumbnail($name);

        $function = $modifier ?? $function;

        $disk = Storage::disk('public');

        $disk->makeDirectory($this->getDir($name));

        $storePath = ($name == 'original') ? $this->getInstance()->getPathname() : $disk->path($this->getPath());

        $image = \Image::make($storePath);

        $thumb = $function($image, $this->getObject());

        $thumb->save(
            $disk->path($this->getPath($name))
        );

        return $thumb;
    }

    public function createThumbnails()
    {
        foreach ($this->object->getImageThumbnails() as $name => $modifier) {
            $this->createThumbnail($name);
        }
    }

    public function removeThumbnail($name)
    {
        $this->assertThumbnailExists($name);

        Storage::disk('public')->delete($this->getPath($name));

        return true;
    }

    public function removeThumbnails()
    {
        foreach ($this->object->getImageThumbnails() as $name => $modifier) {
            $this->removeThumbnail($name);
        }
    }

    /**
     * @param $name
     * @return bool|\Intervention\Image\Image
     * @throws \Exception
     */
    public function recreateThumbnail($name)
    {
        $this->assertThumbnailExists($name);

        return $this->createThumbnail($name);
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