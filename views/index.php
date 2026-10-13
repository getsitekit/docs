<?php $view->script('docs', 'docs:app/bundle/docs.js', 'vue') ?>
<?php $view->style('docs', 'docs:assets/docs.css') ?>

<div class="tm-grid tm-grid-divider">

    <div class="tm-width-medium-1-4">

        <ul class="tm-nav tm-nav-side">
            <?php foreach ($categories as $category) : ?>
            <li>
                <div class="tm-nav-header"><?= $category->title ?></div>

                <?php if ($posts = $category->getPosts()) : ?>
                <ul class="tm-nav-sub">
                    <?php foreach ($posts as $sub_post) : ?>
                    <li<?= $sub_post->id == $post->id ? ' class="tm-active"' : '' ?>>
                        <a href="<?= $view->url('@docs/id', ['id' => $sub_post->id]) ?>"><?= $sub_post->title ?></a>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </li>
            <?php endforeach ?>
        </ul>

    </div>

    <div class="tm-width-medium-1-2">

        <article class="tm-article">

            <h1 class="tm-article-title"><?= $post->title ?></h1>

            <header class="tm-comment-header js-docs-meta">
                <img class="tm-comment-avatar tm-border-circle" width="40" height="40" alt="<?= $this->escape($post->user->name) ?>" v-gravatar="'<?= $this->escape($post->user->email) ?>'">
                <h3 class="tm-comment-title"><?= $this->escape($post->user->name) ?></h3>
                <p class="tm-comment-meta">
                    <time v-cloak datetime="<?= $post->modified->format(\DateTime::ATOM) ?>">{{ $relativeDate("<?= $post->modified->format(\DateTime::ATOM) ?>") }}</time>
                </p>
            </header>

            <div class="tm-margin"><?= $post->content ?></div>

        </article>

    </div>

    <div class="tm-width-medium-1-4">

        <?php if ($post->links) : ?>
        <ul class="tm-nav tm-nav-side">
            <?php foreach ($post->links as $link) : ?>
            <li><a href="#<?= $link['src'] ?>"><?= $link['name'] ?></a></li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>

        <?php if ($sourceedit = $post->get('sourceedit')) : ?>
        <hr>
        <p><a class="tm-link" href="<?= $this->escape($sourceedit) ?>" target="_blank" rel="nofollow"><?= __('Edit') ?></a></p>
        <?php endif ?>

    </div>

</div>
