<?php

namespace Sitekit\Docs\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\Annotation\BelongsTo;
use Pagekit\Database\ORM\Annotation\Column;
use Pagekit\Database\ORM\Annotation\Entity;
use Pagekit\Database\ORM\Annotation\Id;
use Pagekit\System\Model\DataModelTrait;

#[Entity(tableClass: '@docs_post')]
// SiteController 会给实体动态赋值 links 属性；PHP 8.3 下需显式允许动态属性
#[\AllowDynamicProperties]
class Docs implements \JsonSerializable
{
    use DocsModelTrait, DataModelTrait;

    const STATUS_DRAFT = 0;
    const STATUS_PENDING_REVIEW = 1;
    const STATUS_UNPUBLISHED = 2;
    const STATUS_PUBLISHED = 3;

    #[Column(type: 'integer')]
    #[Id]
    public $id;

    #[Column(type: 'integer')]
    public $user_id;

    #[Column(type: 'integer')]
    public $category_id;

    #[Column(type: 'string')]
    public $title;

    #[Column(type: 'string')]
    public $slug;

    #[Column(type: 'integer')]
    public $status;

    #[Column(type: 'datetime')]
    public $date;

    #[Column(type: 'datetime')]
    public $modified;

    #[Column(type: 'text')]
    public $content;

    #[Column(type: 'integer')]
    public $priority = 999;

    #[BelongsTo(targetEntity: 'Pagekit\User\Model\User', keyFrom: 'user_id')]
    public $user;

    #[BelongsTo(targetEntity: 'Category', keyFrom: 'category_id')]
    public $category;

    /** @var array */
    protected static $properties = [
        'category_name' => 'getCategory',
        'published' => 'isPublished',
        'accessible' => 'isAccessible'
    ];

    public static function getStatuses()
    {
        return [
            self::STATUS_PUBLISHED => __('Published'),
            self::STATUS_UNPUBLISHED => __('Unpublished'),
            self::STATUS_DRAFT => __('Draft'),
            self::STATUS_PENDING_REVIEW => __('Pending Review')
        ];
    }

    public function getStatusText()
    {
        $statuses = self::getStatuses();

        return isset($statuses[$this->status]) ? $statuses[$this->status] : __('Unknown');
    }

    /**
     * @return string|void
     */
    public function getCategory()
    {
        if ($this->category) {
            return $this->category->title;
        }
        return;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED && $this->date < new \DateTime;
    }

    /**
     * @return bool
     */
    public function isAccessible()
    {
        return $this->isPublished();
    }

    public function hasAccess()
    {
        $query = Category::where(['status = ?', 'id = ?'], [self::STATUS_PUBLISHED, $this->category_id])->where(function ($query) {
            return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
        })->first();

        if ($query) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): mixed
    {
        $data = [
            'url' => App::url('@docs/id', ['id' => $this->id ?: 0], 'base')
        ];

        return $this->toArray($data);
    }
}
