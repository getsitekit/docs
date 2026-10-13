<template>

    <div class="sk-grid sk-grid-large sk-width-sidebar-large sk-form-stacked" data-sk-grid-margin>
        <div class="sk-width-content">

            <div class="sk-form-row">
                <input class="sk-width-1-1 sk-form-large" type="text" name="title" :placeholder="$trans('Enter Title')" v-model="query.title">
            </div>
            <div class="sk-form-row">
                <v-editor id="docs-content" v-model:value="query.content" :options="{markdown: true, height: 600}"></v-editor>
            </div>

        </div>
        <div class="sk-width-sidebar">

            <div class="sk-panel">

                <div class="sk-form-row">
                    <label for="form-slug" class="sk-form-label">{{ $trans('Slug') }}</label>
                    <div class="sk-form-controls">
                        <input id="form-slug" class="sk-width-1-1" type="text" v-model="query.slug">
                    </div>
                </div>
                <div class="sk-form-row">
                    <label for="form-status" class="sk-form-label">{{ $trans('Status') }}</label>
                    <div class="sk-form-controls">
                        <select id="form-status" class="sk-width-1-1" v-model.number="query.status">
                            <option v-for="(status, id) in data.statuses" :value="id">{{ status }}</option>
                        </select>
                    </div>
                </div>
                <div class="sk-form-row">
                    <label for="form-category" class="sk-form-label">{{ $trans('Category') }}</label>
                    <div class="sk-form-controls">
                        <select id="form-category" class="sk-width-1-1" v-model.number="query.category_id">
                            <option v-for="category in data.categories" :value="category.id">{{ category.title }}</option>
                        </select>
                    </div>
                </div>
                <div class="sk-form-row">
                    <span class="sk-form-label">{{ $trans('Date') }}</span>
                    <div class="sk-form-controls">
                        <input-date v-model:datetime="query.date"></input-date>
                    </div>
                </div>
                <div class="sk-form-row">
                    <label for="form-sourceedit" class="sk-form-label">{{ $trans('Source Edit Link') }}</label>
                    <div class="sk-form-controls">
                        <input id="form-sourceedit" class="sk-width-1-1" type="url" placeholder="https://github.com" v-model="query.data.sourceedit">
                    </div>
                </div>

            </div>

        </div>
    </div>

</template>

<script>

    module.exports = {

        props: ['query', 'data'],

        section: {
            label: 'Settings',
            priority: 0
        }

    };

</script>
