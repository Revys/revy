<?php

namespace Revys\Revy\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Revys\Revy\App\Helpers\Tree;
use Revys\RevyAdmin\App\RevyAdmin;

/**
 * @property int id
 * @property array translatedAttributes
 * @todo Exclude hidden/published methods into thread
 */
class EntityBase extends Model
{
    const STATUS_HIDDEN = 0;
    const STATUS_PUBLISHED = 1;

    protected $guarded = ['id'];

    /**
     * @return string
     */
    public static function getUrlIDField()
    {
        return 'slug';
    }

    /**
     * @return string
     */
    public static function getStringIdField()
    {
        return 'sid';
    }

    /**
     * @return string
     */
    public function getUrlID() {
        return ($this->{$this->getUrlIDField()}) ?? null;
    }

    /**
     * @return string
     */
    public function getStringID() {
        return ($this->{$this->getStringIDField()}) ?? null;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getModelName()
    {
        return class_basename(static::class);
    }

    /**
     * Находит объект по уникальному полю slug
     * @param string $slug
     * @return Page
     */
    public static function findByUrlID($slug)
	{
		return static::where(static::getUrlIDField(), '=', $slug)->first();
	}

    /**
     * Находит объект по уникальному полю name_id
     */
    public static function findByUID($sid)
	{
		return static::where(static::getStringIdField(), '=', $sid)->first();
	}

	/**
	 * Нормализует мета-данные для отображения на странице
     *
     * @todo Получать значения переменных [default_meta_title], [default_meta_description] и [default_meta_keywords] из базы данных
	 */
    public function assignMeta()
    {
//        $default_meta_title = '';
//        $default_meta_description = '';
//        $default_meta_keywords = '';
//
//        $this->meta_title = ($this->meta_title) ? $this->meta_title : $default_meta_title;
//
//        $meta_description = ($this->meta_description) ? $this->meta_description : $default_meta_description;
//        $meta_keywords = ($this->meta_keywords) ? $this->meta_keywords : $default_meta_keywords;
//
//        if ($meta_description !== '')
//            $this->meta_description = $meta_description;
//
//        if ($meta_keywords !== '')
//            $this->meta_keywords = $meta_keywords;
//
//        return $this;
    }

    /**
     * Отбирает только опубликованные объекты
     * @param Builder $query
     */
    public function scopePublished(Builder $query)
    {
        $query->where('status', '=', self::STATUS_PUBLISHED);
    }

    /**
     * Скрыт ли объект
     */
    public function isHidden()
    {
        return (isset($this->status) && $this->status == self::STATUS_HIDDEN);
    }

    /**
     * Опубликован ли объект
     */
    public function isPublished()
    {
        return (! isset($this->status) || $this->status == self::STATUS_PUBLISHED);
    }

    /** 
     *  Возвращает массив с ключом и значением без выбранного элемента
     */
    public static function getListForRelation($object = null, $keyField = 'id', $titleField = 'title')
    {
        $id = isset($object) ? $object->id : 0;
        $items = static::where('id', '!=', $id)->get()->toArray();

        $items = Tree::sort($items);

        $newItems = $items;
        $items = array();

        foreach ($newItems as $item) {
            $items[$item[$keyField]] = str_repeat('-- ', $item['level']) . $item[$titleField];
        }

        return ['' => __('-- Без родителя')] + $items;
    }

    /**
     *  Публикет объект
     */
    public function publish()
    {
        $this->status = self::STATUS_PUBLISHED;

        return $this->save();
    }
    
    /**
     *  Публикет объект
     */
    public function hide()
    {
        $this->status = self::STATUS_HIDDEN;

        return $this->save();
    }
    
    /**
     * Validation default rules
     * 
     * Prepare rules
     */
    public static function getRules()
    {
        $rules = static::rules();

        if (RevyAdmin::isTranslationMode()) {
            $object = new static();

            foreach ($rules as $field => $rule) {
                if ($object->isTranslatableField($field)) {
                    unset($rules[$field]);
                    foreach (Language::getLocales() as $locale) {
                        $rules[RevyAdmin::getTranslationFieldName($field, $locale)] = $rule;
                    }
                }
            }
        }
      
        return $rules;
    }

    /**
     * Validation default rules array
     */
    public static function rules()
    {
        return [];
    }

    /**
     * Validation default messages
     */
    public static function messages()
    {
        return [
            'required' => __('Это обязательное поле')
        ];
    }

    /**
     * This scope eager loads the translations for the default and the fallback locale only.
     * We can use this as a shortcut to improve performance in our application.
     *
     * @param Builder $query
     */
    public function scopeWithTranslation(Builder $query)
    {
        if (self::translatable())
            parent::scopeWithTranslation($query);
    }

    public static function translatable()
    {
        return isset(static::$translatedAttributes);
    }

    public static function isTranslatableField($field)
    {
        if (! self::translatable())
            return false;

        return in_array($field, static::$translatedAttributes);
    }
}
