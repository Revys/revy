<?php

namespace Revys\Revy\App;

use Revys\Revy\App\Entity;
use Revys\Revy\App\Traits\Translatable;

class TextblockBase extends Entity
{
    use Translatable;

    public static $translatedAttributes = ['title', 'text'];

    protected $table = 'textblock';

    public static function rules()
    {
        return [
            'title' => 'required'
        ];
    }

    public static function getText($uid)
    {
        $textblock = self::findByUID($uid);

        if ($textblock) {
            return $textblock->text;
        }

        return '';
    }
}
