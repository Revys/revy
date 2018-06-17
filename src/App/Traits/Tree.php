<?php

namespace Revys\Revy\App\Traits;

trait Tree
{
    public function parent()
    {
        $result = $this->hasOne($this->getModel(), 'id', 'parent_id');

        if (static::translatable())
            $result->withTranslation();

        return $result;
    }
    
    public function children()
    {
        $result = $this->hasMany($this->getModel(), 'parent_id', 'id');

        if (static::translatable())
            $result->withTranslation();

        return $result;
    }
    
    public static function tree($level = 4, $published = false)
    {
        $with = array();

        if ($published) {
            for ($i = 1; $i < $level; $i++) { 
                $with[implode('.', array_fill(0, $i, 'children'))] = function ($q) {
                    $q->published();
                };
            }
        } else {
            $with[] = implode('.', array_fill(0, $level, 'children'));
        }

        $result = static::with($with)->where('parent_id', '=', NULL);

        if ($published)
            $result->published();

        if (static::translatable())
            $result->withTranslation();

        return $result;
    }
    
    public static function treePublished($level = 4)
    {
        return static::tree($level, true);
    }

    public function scopeFirstLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}