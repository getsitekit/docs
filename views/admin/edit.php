<?php $view->script('docs-edit', 'docs:app/bundle/edit.js', ['vue', 'editor', 'sk']) ?>

<form id="docs-edit" class="sk-form" @submit.prevent="save" v-cloak>

    <div class="sk-margin sk-flex sk-flex-space-between sk-flex-wrap" data-sk-margin>
        <div data-sk-margin>

            <h2 class="sk-margin-remove" v-if="query.id">{{ $trans('Edit Docs') }}</h2>
            <h2 class="sk-margin-remove" v-else>{{ $trans('Add Docs') }}</h2>

        </div>
        <div data-sk-margin>

            <a class="sk-button sk-margin-small-right" :href="$url.route('admin/docs/post')">{{ $trans(query.id ? 'Close' : 'Cancel') }}</a>
            <button class="sk-button sk-button-primary" :disabled="!query.title" type="submit">{{ $trans('Save') }}</button>

        </div>
    </div>

    <ul class="sk-tab" ref="tab" v-show="sections.length > 1">
        <li v-for="section in sections"><a>{{ $trans(section.label) }}</a></li>
    </ul>

    <div class="sk-switcher sk-margin" ref="content">
        <div v-for="section in sections">
            <component :is="section.name" :query="query" :data="data"></component>
        </div>
    </div>

</form>
