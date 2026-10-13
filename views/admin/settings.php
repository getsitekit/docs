<?php $view->script('docs-settings', 'docs:app/bundle/settings.js', 'vue') ?>

<div id="settings" class="sk-form sk-form-horizontal" v-cloak>

    <div class="sk-grid sk-grid-large" data-sk-grid-margin>
        <div class="sk-width-sidebar">

            <div class="sk-panel">

                <ul class="sk-nav sk-nav-side sk-nav-large" data-sk-tab="{ connect: '#tab-content' }">
                    <li><a><i class="sk-margin-right" data-sk-icon="menu" data-sk-icon-size="25"></i> {{ $trans('General') }}</a></li>
                </ul>

            </div>

        </div>
        <div class="sk-width-content">

            <ul id="tab-content" class="sk-switcher sk-margin">
                <li>

                    <div class="sk-margin sk-flex sk-flex-space-between sk-flex-wrap" data-sk-margin>
                        <div data-sk-margin>

                            <h2 class="sk-margin-remove">{{ $trans('General') }}</h2>

                        </div>
                        <div data-sk-margin>

                            <button class="sk-button sk-button-primary" @click.prevent="save">{{ $trans('Save') }}</button>

                        </div>
                    </div>

                    <div class="sk-form-row">
                        <span class="sk-form-label">{{ $trans('Permalink') }}</span>
                        <div class="sk-form-controls sk-form-controls-text">
                            <p class="sk-form-controls-condensed">
                                <label>
                                    <input type="radio" v-model="config.permalink.type" value="">
                                    {{ $trans('Numeric') }} <code>{{ $trans('/123') }}</code>
                                </label>
                            </p>
                            <p class="sk-form-controls-condensed">
                                <label>
                                    <input type="radio" v-model="config.permalink.type" value="{slug}">
                                    {{ $trans('Name') }} <code>{{ $trans('/sample-post') }}</code>
                                </label>
                            </p>
                            <p class="sk-form-controls-condensed">
                                <label>
                                    <input type="radio" v-model="config.permalink.type" value="{year}/{month}/{day}/{slug}">
                                    {{ $trans('Day and name') }} <code>{{ $trans('/2014/06/12/sample-post') }}</code>
                                </label>
                            </p>
                            <p class="sk-form-controls-condensed">
                                <label>
                                    <input type="radio" v-model="config.permalink.type" value="{year}/{month}/{slug}">
                                    {{ $trans('Month and name') }} <code>{{ $trans('/2014/06/sample-post') }}</code>
                                </label>
                            </p>
                            <p class="sk-form-controls-condensed">
                                <label>
                                    <input type="radio" v-model="config.permalink.type" value="custom">
                                    {{ $trans('Custom') }}
                                </label>
                                <input class="sk-form-small" type="text" v-model="config.permalink.custom">
                            </p>
                        </div>
                    </div>

                    <div class="sk-form-row">
                        <span class="sk-form-label">{{ $trans('Posts per page') }}</span>
                        <div class="sk-form-controls sk-form-controls-text">
                            <p class="sk-form-controls-condensed">
                                <input type="number" v-model="config.posts.posts_per_page" class="sk-form-width-small">
                            </p>
                        </div>
                    </div>

                </li>
            </ul>

        </div>
    </div>

</div>
