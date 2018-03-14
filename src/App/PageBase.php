<?php

namespace Revys\Revy\App;

/**
 * @property string slug
 * @property string sid
 * @property string meta_title
 * @property string meta_description
 * @property string meta_keywords
 */
class PageBase extends Entity
{
    /**
     *  Get page uri path
     *
     * @return string
     */
    public function getPath()
    {
        $lang = \Revy::getLocale();

        if ($this->getStringID() == 'index')
            return '/';

        return '/' . $lang . '/' . $this->getUrlID();
    }
}
