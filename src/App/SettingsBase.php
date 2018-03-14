<?php

namespace Revys\Revy\App;

class SettingsBase extends Entity
{
    /**
     * @param string $name_id
     * @return mixed
     */
    public static function value($name_id)
    {
        $setting = self::findByUID($name_id);

        if ($setting)
            return $setting->value;
    }
}
