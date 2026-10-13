<?php

namespace Sitekit\Docs\Controller;

use Sitekit\Docs\Model\Category;
use Sitekit\Docs\Model\Docs;
use Pagekit\Application as App;
use Pagekit\Routing\Annotation\Request;
use Pagekit\Routing\Annotation\Route;
use Pagekit\User\Annotation\Access;

// 所有后台 API 要求 docs: manage docs（与菜单一致）；admin: true 必须保留 —— AccessListener 靠它把路径前缀成 /admin（Administrator 在 User::hasAccess() 里直接放行）
#[Access('docs: manage docs', admin: true)]
#[Route('/api', name: 'api')]
class ApiDocsController
{
    #[Route(methods: 'GET')]
    #[Request(['filter' => 'array', 'page' => 'int'])]
    public function indexAction($filter = [], $page = 0)
    {
        $query = Docs::query();
        $filter = array_merge(array_fill_keys(['status', 'search', 'category_id', 'order', 'limit'], ''), $filter);
        extract($filter, EXTR_SKIP);

        if ($search) {
            $query->where(function ($query) use ($search) {
                return $query->orWhere(['title LIKE :search', 'slug LIKE :search'], ['search' => "%$search%"]);
            });
        }

        if ($category_id) {
            $query->where('category_id = ?', [$category_id]);
        }

        if (!preg_match('/^(date|title|priority)\s(asc|desc)$/i', $order, $order)) {
            $order = [1 => 'priority', 2 => 'asc'];
        }

        $limit = (int) $limit ?: 10;
        $count = $query->count();
        $pages = ceil($count / $limit);
        $page  = max(0, min($pages - 1, $page));

        $posts = array_values($query->offset($page * $limit)->limit($limit)->related(['user', 'category'])->orderBy($order[1], $order[2])->get());
        return compact('posts', 'pages', 'count', 'filter');
    }

    #[Route(methods: 'POST')]
    #[Request(['data' => 'array', 'id' => 'int'])]
    public function saveAction($data = [], $id = 0)
    {
        if (!$query = Docs::where(compact('id'))->first()) {
            if ($id) {
                return App::abort(404, __('Not Found Category'));
            }
            $query = Docs::create([
                'user_id' => App::user()->id,
                'date' => new \DateTime(),
                'status' => Docs::STATUS_DRAFT
            ]);
        }

        if (!$data['slug'] = App::filter($data['slug'] ?: $data['title'], 'slugify')) {
            return App::abort(400, __('Invalid slug.'));
        }

        $query->save($data);
        return compact('query');
    }

    #[Route(methods: 'POST')]
    #[Request(['category' => 'array', 'id' => 'int'])]
    public function saveCategoryAction($category = [], $id = 0)
    {
        if (!$query = Category::where(compact('id'))->first()) {
            if ($id) {
                return App::abort(404, __('Not Found Category'));
            }
            $query = Category::create([
                'date' => new \DateTime()
            ]);
        }

        if (!$category['slug'] = App::filter($category['slug'] ?: $category['title'], 'slugify')) {
            return App::abort(400, __('Invalid slug.'));
        }

        $query->save($category);
        return compact('query');
    }

    #[Route(methods: 'GET')]
    #[Request(['id' => 'int'])]
    public function bulkCategoryDeleteAction($id = 0)
    {
        if (!$category = Category::find($id)) {
            return App::abort(404, __('Not Found Category'));
        }
        $category->delete();
        return [
            'msg' => true
        ];
    }

    #[Route('/bulk', methods: 'POST')]
    #[Request(['posts' => 'array'])]
    public function bulkSaveAction($posts = [])
    {
        foreach ($posts as $data) {
            $this->saveAction($data, isset($data['id']) ? $data['id'] : 0);
        }

        return ['message' => 'success'];
    }

    #[Route('/bulk', methods: 'DELETE')]
    #[Request(['ids' => 'array'])]
    public function bulkDeleteAction($ids = [])
    {
        foreach (array_filter($ids) as $id) {
            $this->deleteAction($id);
        }

        return ['message' => 'success'];
    }

    #[Route('/{id}', methods: 'DELETE', requirements: ['id' => '\d+'])]
    #[Request(['id' => 'int'])]
    public function deleteAction($id)
    {
        if ($post = Docs::find($id)) {
            $post->delete();
        }

        return ['message' => 'success'];
    }
}
