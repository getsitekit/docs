<?php

/** 示例数据：安装/启用扩展时写入一批参考内容；按 slug 判断幂等，用原生 SQL 写入（启用瞬间 ORM 自动加载尚未生效）。 */
$seed = function ($app) {

    $db = $app['db'];

    $now  = date('Y-m-d H:i:s');
    // 比当前时间稍早，避免前台 `date < now` 的过滤把刚写入的文档挡掉
    $date = date('Y-m-d H:i:s', time() - 600);

    $categories = [
        [
            'title' => '开始使用',
            'slug' => 'getting-started',
            'docs' => [
                [
                    'title' => '安装指南',
                    'slug' => 'install',
                    'content' => "本扩展用于编写产品/应用的文档，前台按「分类 → 文档」两级组织内容。\n\n"
                        . "## 后台在哪里维护\n\n"
                        . "后台顶部菜单「文档」→ 列表页左侧新增分类，右上角新增文档；分类支持拖拽排序，"
                        . "文档支持搜索、状态筛选与批量发布。\n\n"
                        . "## 前台入口\n\n"
                        . "扩展启用后会自动创建 `/docs` 页面节点（受保护类型），可在「网站 → 页面」里调整标题与别名。\n\n"
                        . "## 小提示\n\n"
                        . "新建的文档默认是「草稿」，记得把状态改成「已发布」并确认日期已到，才会出现在前台。",
                ],
                [
                    'title' => '目录结构',
                    'slug' => 'directory-structure',
                    'content' => "## 数据表\n\n"
                        . "- `@docs_category`：分类（title、slug、status、priority、roles、data）\n"
                        . "- `@docs_post`：文档（title、slug、status、date、content、priority、category_id、user_id、data）\n\n"
                        . "## 代码结构\n\n"
                        . "- `src/Controller`：后台页面、后台 API、前台页面\n"
                        . "- `src/Model`：Category / Docs 两个实体\n"
                        . "- `views/`：PHP 模板；`app/`：Vue 组件与打包产物（`app/bundle/*.js`）",
                ],
            ],
        ],
        [
            'title' => '使用指南',
            'slug' => 'usage',
            'docs' => [
                [
                    'title' => '创建文档',
                    'slug' => 'create-docs',
                    'content' => "## 步骤\n\n"
                        . "1. 先在列表页左侧点「添加分类」，填标题即可（别名会按标题自动生成）。\n"
                        . "2. 再点右上角「添加文档」，填写标题与正文。\n"
                        . "3. 在右侧栏设置别名、状态、分类、日期，最后点「保存」。\n\n"
                        . "## 正文格式\n\n"
                        . "正文统一按 Markdown 渲染；正文里的 `#`、`##` 标题会自动出现在前台右侧的页内目录中。",
                ],
                [
                    'title' => '分类与排序',
                    'slug' => 'categories',
                    'content' => "## 排序\n\n"
                        . "- 分类：在列表页左侧直接拖拽，松开后自动保存排序（priority）。\n"
                        . "- 文档：列表页同样支持拖拽排序。\n\n"
                        . "## 状态说明\n\n"
                        . "分类与文档的状态取值为：0 草稿 / 1 待审 / 2 未发布 / 3 已发布。"
                        . "只有「已发布」且日期已到的文档会出现在前台。",
                ],
            ],
        ],
        [
            'title' => '进阶',
            'slug' => 'advanced',
            'docs' => [
                [
                    'title' => '固定链接设置',
                    'slug' => 'permalinks',
                    'content' => "后台「文档 → 设置」可以调整前台 URL 形式：\n\n"
                        . "- 数字型：`/docs/123`\n"
                        . "- 名字：`/docs/sample-post`\n"
                        . "- 日期文档型 / 月份文档型：`/docs/2026/10/13/sample-post`\n"
                        . "- 自定义：自行组合 `{slug}`、`{year}`、`{month}`、`{day}` 等占位符\n\n"
                        . "同一页还可以设置每页文档数与默认 Markdown 开关。",
                ],
                [
                    'title' => '常见问题',
                    'slug' => 'faq',
                    'content' => "## 前台看不到文档？\n\n"
                        . "依次检查：文档状态是否为「已发布」、文档日期是否已到、所属分类状态是否为「已发布」。\n\n"
                        . "## 分类被限制访问？\n\n"
                        . "分类的 `roles` 为空表示所有人可见；一旦勾选了角色，就只有该角色能访问，其它访问者在前台会看到 404。",
                ],
            ],
        ],
    ];

    // fetchAllKeyValue() 以**第一列**为键，因此这里把 slug 放在第一列 → [slug => id]
    $existingCategories = $db->executeQuery('SELECT slug, id FROM @docs_category')->fetchAllKeyValue();
    $existingDocs       = $db->executeQuery('SELECT slug FROM @docs_post')->fetchFirstColumn();

    $userId = (int) $db->executeQuery('SELECT id FROM @system_user ORDER BY id')->fetchOne();
    $userId = $userId ?: 1;

    $categoryPriority = 0;

    foreach ($categories as $category) {

        if (isset($existingCategories[$category['slug']])) {
            $categoryId = (int) $existingCategories[$category['slug']];
        } else {
            $db->insert('@docs_category', [
                'title'    => $category['title'],
                'slug'     => $category['slug'],
                'status'   => 3,
                'priority' => $categoryPriority,
                'roles'    => null,
                'data'     => null,
            ]);

            $categoryId = (int) $db->lastInsertId();
        }

        $categoryPriority++;
        $docPriority = 0;

        foreach ($category['docs'] as $doc) {

            if (in_array($doc['slug'], $existingDocs, true)) {
                $docPriority++;
                continue;
            }

            $db->insert('@docs_post', [
                'user_id'     => $userId,
                'category_id' => $categoryId,
                'title'       => $doc['title'],
                'slug'        => $doc['slug'],
                'status'      => 3,
                'date'        => $date,
                'modified'    => $now,
                'content'     => $doc['content'],
                'priority'    => $docPriority,
                // 正文统一强制按 Markdown 渲染（无单篇开关），data 留空即可
                'data'        => null,
            ]);

            $docPriority++;
        }
    }
};

return [

    // 使用 install/uninstall 回调（与 blog 扩展一致）
    'install' => function ($app) use ($seed) {

        $util = $app['db']->getUtility();

        if ($util->tableExists('@docs_post') === false) {
            $util->createTable('@docs_post', function ($table) {
                $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'length' => 10]);
                $table->addColumn('user_id', 'integer', ['notnull' => false]);
                $table->addColumn('category_id', 'integer');
                $table->addColumn('title', 'string');
                $table->addColumn('slug', 'string');
                $table->addColumn('status', 'integer');
                $table->addColumn('date', 'datetime');
                $table->addColumn('modified', 'datetime', ['notnull' => false]);
                $table->addColumn('content', 'text', ['notnull' => false]);
                $table->addColumn('priority', 'integer', ['default' => 999]);
                $table->addColumn('data', 'json_array', ['notnull' => false]);
                $table->setPrimaryKey(['id']);
                $table->addIndex(['title'], '@DOCS_POST_TITLE');
                $table->addIndex(['slug'], '@DOCS_POST_SLUG');
            });
        }

        if ($util->tableExists('@docs_category') === false) {
            $util->createTable('@docs_category', function ($table) {
                $table->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'length' => 10]);
                $table->addColumn('title', 'string');
                $table->addColumn('slug', 'string');
                $table->addColumn('status', 'integer');
                $table->addColumn('priority', 'integer', ['default' => 999]);
                $table->addColumn('roles', 'simple_array', ['notnull' => false]);
                $table->addColumn('data', 'json_array', ['notnull' => false]);
                $table->setPrimaryKey(['id']);
                $table->addIndex(['title'], '@DOCS_CATEGORY_TITLE');
                $table->addIndex(['slug'], '@DOCS_CATEGORY_SLUG');
            });
        }

        // 安装时写入示例数据（幂等），供首次使用参考
        $seed($app);
    },

    'uninstall' => function ($app) {

        $util = $app['db']->getUtility();

        if ($util->tableExists('@docs_post')) {
            $util->dropTable('@docs_post');
        }

        if ($util->tableExists('@docs_category')) {
            $util->dropTable('@docs_category');
        }
    },

    // 单独提供种子数据入口：需要时（如已有站点补数据）可再跑一次，重复执行不会产生重复数据
    'seed' => $seed,

    'updates' => []

];
