<?php

namespace Revys\Revy\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Revys\Revy\App\Image;

class ImageAdded
{
    use Dispatchable;

    public $image;

    /**
     * @param Image $image
     */
    public function __construct($image)
    {
        $this->image = $image;
    }
}
