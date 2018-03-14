<?php

namespace Revys\Revy\App;

class MenuBase extends Entity
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'menu';

    public static function getBlock($name)
    {
        return Menu::latest()->published()->get();
    }
}
