<?php

namespace Sitekit\Docs\Model;

use Pagekit\Application as App;
use Pagekit\Database\ORM\Annotation\Column;
use Pagekit\Database\ORM\Annotation\Entity;
use Pagekit\Database\ORM\Annotation\HasMany;
use Pagekit\Database\ORM\Annotation\Id;
use Pagekit\System\Model\DataModelTrait;
use Pagekit\User\Model\AccessModelTrait;

#[Entity(tableClass: '@docs_category')]
class Category implements \JsonSerializable
{
    use CategoryModelTrait, DataModelTrait, AccessModelTrait;

    const STATUS_DRAFT = 0;
    const STATUS_PENDING_REVIEW = 1;
    const STATUS_UNPUBLISHED = 2;
    const STATUS_PUBLISHED = 3;

    #[Column(type: 'integer')]
    #[Id]
    public $id;

    #[Column(type: 'string')]
    public $title;

    #[Column(type: 'string')]
    public $slug;

    #[Column(type: 'integer')]
    public $status;

    #[Column(type: 'integer')]
    public $priority = 999;

    #[HasMany(targetEntity: 'Docs', keyFrom: 'id', keyTo: 'category_id')]
    public $posts;

    /** @var array */
    protected static $properties = [
        'hasPost' => 'hasPost',
        'getPosts' => 'getPosts'
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
     * @return bool
     */
    public function hasPost(): bool
    {
        if ($query = Docs::where('category_id = ?', [$this->id])->first()) {
            return true;
        }
        return false;
    }

    public function getPosts()
    {
        if ($this->posts) {
            $query = Docs::where(['status = ?', 'date < ?', 'category_id = ?'], [self::STATUS_PUBLISHED, new \DateTime(), $this->id])->orderBy('priority', 'asc')->get();
            return $query;
        }
        return false;
    }

    public static function getFirstPost()
    {
        // 取全站第一篇「已发布且日期已到」的文档；分类权限由 SiteController 的 $post->hasAccess() 校验
        $query = Docs::where(['status = ?', 'date < ?'], [self::STATUS_PUBLISHED, new \DateTime])->orderBy('priority', 'asc')->first();
        if (!$query) {
            return false;
        }
        return $query->id;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
