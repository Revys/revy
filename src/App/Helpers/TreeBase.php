<?php

namespace Revys\Revy\App\Helpers;

class TreeBase
{
    public static function sort($items)
    {       
        $parents = $new = array();
        
        foreach ($items as $item) {
            if ((int) $item['parent_id'])
                $parents[$item['parent_id']][] = $item;
        }
    
        foreach ($items as $item) {
            if ((int) $item['parent_id'] == 0) {
                $item['level'] = 0;
                $new[] = $item;

                $childs = self::findChilds($item['id'], $parents, 1);

                if (isset($childs) and count($childs))
                    foreach ($childs as $child)
                        $new[] = $child;
            }
        }

        return $new;
    }

    public static function findChilds($id, $parents, $level = 0)
    {
        $new = array();

        if (isset($parents[$id])) {
            foreach ($parents[$id] as $item) {
                $item['level'] = $level;
                $new[] = $item;

                $childs = self::findChilds($item['id'], $parents, $level + 1);

                if (count($childs))
                    foreach ($childs as $child)
                        $new[] = $child;
            }
        }

        return $new;
    }
}
