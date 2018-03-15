<?php

namespace Revys\Revy\App;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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

    /**
     * @param $name
     * @return \Intervention\Image\Image
     * @throws \Exception
     * @todo Create thumbnails on ImageAddEvent
     */
    public function createThumbnail($name)
    {
        $modifier = $this->object->getImageThumbnails()[$name] ?: '';

        if ($modifier === '') {
            throw new \Exception(
                'Thumbnail with name "' . $name . '" does not exists at model ' . $this->object->getMorphClass()
            );
        }

        Storage::disk('public')->makeDirectory($this->getDir($name));

        $thumb = $modifier($this, $this->getObject());

        $thumb->save(
            Storage::disk('public')->path($this->getPath($name))
        );

        return $thumb;
    }
}