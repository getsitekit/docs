<?php $view->script('docs-index', 'docs:app/bundle/index.js', ['vue', 'sk']) ?>

<div id="docs" class="sk-form" v-cloak>

    <div class="sk-grid sk-grid-large" data-sk-grid-margin>

        <!-- 左侧：分类列表（可拖拽排序） -->
        <div class="sk-width-sidebar">

            <div class="sk-panel">

                <ul class="docs-category sk-nav sk-nav-side sk-sortable" v-if="categories.length">
                    <li class="sk-visible-hover" v-for="category in categories" :key="category.id" :data-id="category.id">
                        <a>
                            <span class="sk-margin-small-right" :title="statuses[category.status]" :class="{
                                'sk-icon-circle-warning': category.status == 0,
                                'sk-icon-circle': category.status == 1,
                                'sk-icon-circle-danger': category.status == 2,
                                'sk-icon-circle-success': category.status == 3
                            }"></span>{{ category.title }}
                        </a>
                        <ul class="sk-subnav sk-subnav-icon sk-hidden">
                            <li><a class="sk-icon-faded" data-sk-icon="edit" data-sk-icon-size="20" :title="$trans('Edit')" data-sk-tooltip="{delay: 500}" @click.prevent="openModal(category)"></a></li>
                        </ul>
                    </li>
                </ul>

                <p class="sk-text-center" v-else>{{ $trans('Not Found Categories') }}</p>

                <p>
                    <a class="sk-button" @click.prevent="openModal(draftCategory)">{{ $trans('Add Category') }}</a>
                </p>

            </div>

        </div>

        <!-- 右侧：文档列表 -->
        <div class="sk-width-content">

            <div class="sk-margin sk-flex sk-flex-space-between sk-flex-wrap" data-sk-margin>
                <div class="sk-flex sk-flex-middle sk-flex-wrap" data-sk-margin>

                    <h2 class="sk-margin-remove" v-if="!selected.length">{{ $transChoice('{0} %count% Doc|{1} %count% Doc|]1,Inf[ %count% Docs', count, {count:count}) }}</h2>

                    <template v-else>
                        <h2 class="sk-margin-remove">{{ $transChoice('{1} %count% Doc selected|]1,Inf[ %count% Docs selected', selected.length, {count:selected.length}) }}</h2>

                        <div class="sk-margin-left">
                            <ul class="sk-subnav sk-subnav-icon">
                                <li><a class="sk-icon-faded" data-sk-icon="check" data-sk-icon-size="20" :title="$trans('Publish')" data-sk-tooltip="{delay: 500}" @click="status(3)"></a></li>
                                <li><a class="sk-icon-faded" data-sk-icon="block" data-sk-icon-size="20" :title="$trans('Unpublish')" data-sk-tooltip="{delay: 500}" @click="status(2)"></a></li>
                                <li><a class="sk-icon-faded" data-sk-icon="delete" data-sk-icon-size="20" :title="$trans('Delete')" data-sk-tooltip="{delay: 500}" @click="remove" v-confirm="'Delete Post?'"></a></li>
                            </ul>
                        </div>
                    </template>

                    <div class="sk-search-divided">
                        <div class="sk-search">
                            <input class="sk-search-field" type="text" v-model="config.filter.search" debounce="300">
                        </div>
                    </div>

                </div>
                <div data-sk-margin>

                    <a class="sk-button sk-button-primary" :href="$url.route('admin/docs/post/edit')" v-if="categories.length">{{ $trans('Add Docs') }}</a>
                    <button class="sk-button sk-button-primary" :title="$trans('You need to add a category first.')" data-sk-tooltip disabled v-else>{{ $trans('Add Docs') }}</button>

                </div>
            </div>

            <div class="sk-overflow-container">
                <table class="sk-table sk-table-hover sk-table-middle">
                    <thead>
                        <tr>
                            <th class="sk-table-width-minimum"><input type="checkbox" v-check-all:selected="'input[name=id]'" number></th>
                            <th class="sk-table-min-width-200" v-order:title="config.filter.order">{{ $trans('Title') }}</th>
                            <th class="sk-table-width-100 sk-text-center">
                                <input-filter :title="$trans('Status')" v-model:value="config.filter.status" :options="statusOptions"></input-filter>
                            </th>
                            <th class="sk-table-width-100 sk-text-center">
                                <input-filter :title="$trans('Category')" v-model:value="config.filter.category_id" :options="categoryOptions"></input-filter>
                            </th>
                            <th class="sk-table-width-200 sk-table-min-width-200">{{ $trans('Url') }}</th>
                        </tr>
                    </thead>
                    <tbody class="docs-post-list sk-sortable">
                        <tr class="check-item" :data-id="post.id" v-for="post in posts" :class="{'sk-active': selected.indexOf(post.id) !== -1}">
                            <td><input type="checkbox" name="id" :value="post.id"></td>
                            <td>
                                <a :href="$url.route('admin/docs/post/edit', { id: post.id })">{{ post.title }}</a>
                            </td>
                            <td class="sk-text-center">
                                <span :title="getStatusText(post)"
                                      :data-sk-icon="post.status == 3 && !post.published ? 'schedule' : ''"
                                      data-sk-icon-size="20"
                                      :class="{
                                          'sk-icon-circle': post.status != 3 || post.published,
                                          'sk-icon-circle-warning': post.status == 0,
                                          'sk-icon-circle-danger': post.status == 2,
                                          'sk-icon-circle-success': post.status == 3 && post.published
                                      }"></span>
                            </td>
                            <td class="sk-text-center">{{ post.category_name }}</td>
                            <td class="sk-table-text-break">
                                <a target="_blank" v-if="post.accessible && post.url" :href="$url.route(post.url.substr(1))">{{ decodeURI(post.url) }}</a>
                                <span v-if="!post.accessible && post.url">{{ decodeURI(post.url) }}</span>
                                <span v-if="!post.url">{{ $trans('Disabled') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h3 class="sk-h1 sk-text-muted sk-text-center" v-show="posts && !posts.length">{{ $trans('No posts found.') }}</h3>

            <v-pagination v-model:page="config.page" :pages="pages" v-show="pages > 1 || config.page > 0"></v-pagination>

        </div>
    </div>

    <!-- 分类新增/编辑弹窗 -->
    <v-modal ref="modal" :closed="onClose">
        <form class="sk-form sk-form-stacked" @submit.prevent="saveCategory(modalDraft, true)">

            <div class="sk-modal-header">
                <h2 v-if="modalDraft.id">{{ $trans('Edit Category') }}</h2>
                <h2 v-else>{{ $trans('New Category') }}</h2>
            </div>

            <div class="sk-form-row">
                <label for="form-category-title" class="sk-form-label">{{ $trans('Title') }}</label>
                <div class="sk-form-controls">
                    <input id="form-category-title" class="sk-width-1-1" type="text" v-model="modalDraft.title" required>
                </div>
            </div>

            <div class="sk-form-row">
                <label for="form-category-slug" class="sk-form-label">{{ $trans('Slug') }}</label>
                <div class="sk-form-controls">
                    <input id="form-category-slug" class="sk-width-1-1" type="text" v-model="modalDraft.slug">
                </div>
            </div>

            <div class="sk-form-row">
                <label for="form-category-status" class="sk-form-label">{{ $trans('Status') }}</label>
                <div class="sk-form-controls">
                    <select id="form-category-status" class="sk-width-1-1" v-model.number="modalDraft.status" required>
                        <option v-for="(status, id) in statuses" :value="id">{{ status }}</option>
                    </select>
                </div>
            </div>

            <div class="sk-form-row">
                <span class="sk-form-label">{{ $trans('Restrict Access') }}</span>
                <div class="sk-form-controls sk-form-controls-text">
                    <p class="sk-form-controls-condensed" v-for="role in roles">
                        <label><input type="checkbox" :value="role.id" v-model="modalDraft.roles" number> {{ role.name }}</label>
                    </p>
                </div>
            </div>

            <div class="sk-modal-footer sk-text-right">
                <button class="sk-button sk-button-link sk-modal-close" type="button" @click.prevent="close">{{ $trans('Close') }}</button>
                <button v-if="modalDraft.id" @click.prevent="deleteCategory(modalDraft.id)" type="button" :disabled="modalDraft.hasPost" class="sk-button sk-button-danger">{{ $trans('Delete') }}</button>
                <button v-if="modalDraft.id" class="sk-button sk-button-link" type="submit">{{ $trans('Save') }}</button>
                <button v-if="!modalDraft.id" class="sk-button sk-button-link" type="submit">{{ $trans('Add') }}</button>
            </div>

        </form>
    </v-modal>

</div>
