<?php

namespace Sitekit\Docs\Controller;

use Sitekit\Docs\Model\Category;
use Sitekit\Docs\Model\Docs;
use Pagekit\Application as App;
use Pagekit\Routing\Annotation\Request;
use Pagekit\Routing\Annotation\Route;
use Pagekit\User\Annotation\Access;
use Pagekit\User\Model\Role;

#[Access(admin: true)]
#[Route(name: 'admin')]
class DocsController
{
    #[Route('/post', name: 'post')]
    #[Access('docs: manage docs')]
    #[Request(['filter' => 'array', 'page' => 'int'])]
    public function indexAction($filter = [], $page = 0): array
    {
        $categories = Category::query()->orderBy('priority', 'asc')->get();

        return [
            '$view' => [
                'title' => __('Docs Index'),
                'name' => 'docs/admin/index.php'
            ],
            '$data' => [
                'config' => [
                    'filter' => (object) $filter,
                    'page' => (int) $page
                ],
                'statuses' => Docs::getStatuses(),
                'roles' => Role::findAll(),
                'categories' => array_values($categories)
            ]
        ];
    }

    #[Route('/post/edit', name: 'post/edit')]
    #[Access('docs: manage docs')]
    #[Request(['id' => 'int'])]
    public function editAction($id = 0)
    {
        $categories = array_values(Category::findAll());

        if (!$categories) {
            return App::abort(400, __('Not Found Categories'));
        }

        if (!$query = Docs::where(compact('id'))->first()) {
            if ($id) {
                return App::abort(404, __('Not Found Docs'));
            }
            $query = Docs::create([
                'user_id' => App::user()->id,
                'date' => new \DateTime(),
                'category_id' => $categories[0]->id,
                'status' => Docs::STATUS_DRAFT
            ]);
        }

        return [
            '$view' => [
                'title' => $query->id ? __('Edit Docs') : __('New Docs'),
                'name' => 'docs/admin/edit.php'
            ],
            '$data' => [
                'data' => [
                    'statuses' => Docs::getStatuses(),
                    'categories' => $categories,
                ],
                'query' => $query
            ]
        ];
    }

    #[Access('system: access settings')]
    public function settingsAction()
    {
        return [
            '$view' => [
                'title' => __('Docs Settings'),
                'name' => 'docs/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('docs')->config()
            ]
        ];
    }
}
