<?php

namespace Revys\Revy\App\Traits;

trait Tree
{
    public function parent()
    {
        return $this->hasOne($this->getModel(), 'id', 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany($this->getModel(), 'parent_id', 'id');
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

        return $result;
    }
    
    public static function treePublished($level = 4)
    {
        return static::tree($level, true);
    }
}