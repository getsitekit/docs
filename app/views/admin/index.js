window.DocsIndex = {

    el: '#docs',

    name: 'DocsIndex',

    data: function () {
        return _.merge({
            categories: false,
            posts: false,
            config: {
                filter: this.$session.get('documents.filter', {order: 'priority asc', limit: 50})
            },
            pages: 0,
            count: '',
            selected: [],
            modalDraft: {id: null, title: null, slug: null, status: 3, roles: []}
        }, window.$data);
    },

    created: function () {
        this.resource = this.$resource('admin/docs/api{/id}');
    },

    ready: function () {

        this.$watch('config.page', this.load, {immediate: true});

        var vm = this,
            list = this.$el.querySelector('.docs-category');

        if (list) {
            SK.sortable(list).element
                .on('change.sk.sortable', function (e, sortable, item, mode) {
                    if (mode === 'moved' || mode === 'added') {
                        vm.reorder(sortable);
                    }
                });
        }

        var postList = this.$el.querySelector('.docs-post-list');

        if (postList) {
            SK.sortable(postList).element
                .on('change.sk.sortable', function (e, sortable, item, mode) {
                    if (mode === 'moved' || mode === 'added') {
                        vm.reorderPosts(sortable);
                    }
                });
        }
    },

    watch: {

        'config.filter': {
            handler: function (filter) {
                if (this.config.page) {
                    this.config.page = 0;
                } else {
                    this.load();
                }

                this.$session.set('documents.filter', filter);
            },
            deep: true
        }

    },

    computed: {

        statusOptions: function () {

            var options = _.map(this.statuses, function (status, id) {
                return {text: status, value: id};
            });

            return [{label: this.$trans('Filter by'), options: options}];
        },

        categoryOptions: function () {

            var options = _.map(this.categories, function (category) {
                return {text: category.title, value: category.id};
            });

            return [{label: this.$trans('Filter by'), options: options}];
        },

        draftCategory: function () {
            return {
                id: null,
                title: null,
                slug: null,
                status: 3,
                roles: []
            };
        }

    },

    methods: {

        load: function () {
            this.resource.query({filter: this.config.filter, page: this.config.page}).then(function (res) {

                var data = res.data;

                this.$set('posts', data.posts);
                this.$set('pages', data.pages);
                this.$set('count', data.count);
                this.$set('selected', []);
            }, function (res) {
                this.$notify(res.data, 'danger');
            });
        },

        reorder: function (sortable) {

            var vm = this;

            sortable.element.children().each(function (index) {

                var id = parseInt(this.getAttribute('data-id'), 10),
                    category = _.find(vm.categories, {id: id});

                if (category && category.priority !== index) {
                    category.priority = index;
                    vm.saveCategory(category);
                }
            });
        },

        saveCategory: function (item, reload) {
            this.$http.post('admin/docs/api/savecategory', {category: item, id: item.id}).then(function () {
                if (!item.id || reload) {
                    location.reload();
                }
            }, function (res) {
                this.$notify(res.data, 'danger');
            });
        },

        reorderPosts: function (sortable) {

            var vm = this;

            sortable.element.children().each(function (index) {

                var id = parseInt(this.getAttribute('data-id'), 10),
                    post = _.find(vm.posts, {id: id});

                if (post && post.priority !== index) {
                    post.priority = index;
                    vm.savePost(post);
                }
            });
        },

        savePost: function (post) {

            // 整条对象回传（接口要求 slug/title 存在才保存，与批量发布/删除一致）
            this.$http.post('admin/docs/api/save', {data: post, id: post.id}).then(function () {
                // 静默成功：拖拽会连续保存多行，逐个弹提示太吵
            }, function (res) {
                this.$notify(res.data, 'danger');
            });
        },

        deleteCategory: function (id) {
            this.$http.get('admin/docs/api/bulkcategorydelete', {params: {id: id}}).then(function () {
                location.reload();
            }, function (res) {
                this.$notify(res.data, 'danger');
            });
        },

        openModal: function (data) {
            this.modalDraft = data;
            this.$refs.modal.open();
        },

        close: function () {
            this.$refs.modal.close();
        },

        onClose: function () {
            this.modalDraft = this.draftCategory;
        },

        status: function (status) {

            var posts = this.getSelected();

            posts.forEach(function (post) {
                post.status = status;
            });

            this.resource.save({id: 'bulk'}, {posts: posts}).then(function () {
                this.load();
                this.$notify('Posts saved.');
            });
        },

        remove: function () {

            this.resource.delete({id: 'bulk'}, {ids: this.selected}).then(function () {
                this.load();
                this.$notify('Posts deleted.');
            });
        },

        getSelected: function () {
            return this.posts.filter(function (post) {
                return this.selected.indexOf(post.id) !== -1;
            }, this);
        },

        getStatusText: function (post) {
            return this.statuses[post.status];
        }

    }

};

Vue.ready(window.DocsIndex);
