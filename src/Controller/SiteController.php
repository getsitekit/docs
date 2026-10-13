<?php

namespace Sitekit\Docs\Controller;

use Sitekit\Docs\Model\Category;
use Sitekit\Docs\Model\Docs;
use Pagekit\Application as App;
use Pagekit\Routing\Annotation\Route;

class SiteController
{
    #[Route('/')]
    #[Route('/{id}', name: 'id')]
    public function indexAction(string $id = '')
    {
        $categories = Category::where(['status = :status'], ['status' => Category::STATUS_PUBLISHED])->where(function ($query) {
            return $query->where('roles IS NULL')->whereInSet('roles', App::user()->roles, false, 'OR');
        })->orderBy('priority', 'asc')->related('posts');

        // 日期参数用 `?` 占位 + 数组传参（本项目对命名参数会推断出 datetime 绑定类型，与字符串化后的值冲突）
        $query = Docs::where(['status = ?', 'date < ?'], [Docs::STATUS_PUBLISHED, new \DateTime]);
        if ($id) {
            $query->where('id = :id', ['id' => $id]);
        } else {
            if (!$getFirstDocs = Category::getFirstPost()) {
                return App::abort(404, __('Not Found Docs'));
            }
            $query->where('id = :id', ['id' => $getFirstDocs]);
        }
        $query->related('user');
        $post = $query->first();

        if (!$post || !$post->hasAccess()) {
            return App::abort(404, __('Not Found Document'));
        }

        // 正文统一按 Markdown 渲染（本扩展没有单篇开关，历史数据里的 data.markdown 也不再生效）
        $post->content = App::content()->applyPlugins($post->content, ['post' => $post, 'markdown' => true]);

        // 本项目 Markdown 渲染器对中文标题会输出空 id（id="-"），这里把不可用的 id 规范成 section-N，否则右侧「页内目录」全部指向 #-
        $section = 0;
        $post->content = preg_replace_callback('/<(h[12])([^>]*)>(.*?)<\/\1>/s', function ($match) use (&$section) {

            $attributes = $match[2];

            if (preg_match('/\sid="([^"]*)"/', $attributes, $id) && trim($id[1], '-') !== '') {
                return $match[0];
            }

            $attributes = preg_replace('/\sid="[^"]*"/', '', $attributes);

            return sprintf('<%s id="section-%d"%s>%s</%s>', $match[1], ++$section, $attributes, $match[3], $match[1]);
        }, $post->content);

        $post->links = $this->doMarkdownLinks($post->content);
        $description = $post->get('meta.og:description');

        if (!$description) {
            $description = strip_tags($post->content);
            $description = rtrim(mb_substr($description, 0, 150), " \t\n\r\0\x0B.,") . '...';
        }

        return [
            '$view' => [
                'title' => $post->title,
                'name' => 'docs/index.php',
                'og:type' => 'article',
                'article:published_time' => $post->date->format(\DateTime::ATOM),
                'article:modified_time' => $post->modified->format(\DateTime::ATOM),
                'article:author' => $post->user->name,
                'og:title' => $post->get('meta.og:title') ?: $post->title,
                'og:description' => $description,
                'og:image' =>  $post->get('image.src') ? App::url()->getStatic($post->get('image.src'), [], 0) : false
            ],
            'post' => $post,
            'categories' => $categories->get()
        ];
    }

    /**
     * @param $s
     * @return array
     */
    protected function doMarkdownLinks($s): array
    {
        $regex = '/<h1?2? id="(.*)">(.*)<\/h1?2?>/';
        preg_match_all($regex, $s, $match);
        $data = [];
        foreach ($match[2] as $key => $value) {
            $data[] = ['name' => $value, 'src' => $match[1][$key]];
        }
        return $data;
    }
}
