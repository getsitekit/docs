window.DocsEdit = {

    el: '#docs-edit',

    name: 'DocsEdit',

    data: function () {
        return _.merge({
            sections: []
        }, window.$data);
    },

    created: function () {

        var sections = [];

        _.forIn(this.$options.components, function (component, name) {

            var options = component.options || {};

            if (options.section) {
                sections.push(_.extend({name: name, priority: 0}, options.section));
            }

        });

        this.$set('sections', _.sortBy(sections, 'priority'));
    },

    ready: function () {

        this.tab = SK.tab(this.$refs.tab, {connect: this.$refs.content});
    },

    methods: {

        save: function () {
            this.$http.post('admin/docs/api/save', {data: this.query, id: this.query.id}).then(function (res) {

                var query = res.data.query;

                if (!this.query.id) {
                    window.history.replaceState({}, '', this.$url.route('admin/docs/post/edit', {id: query.id}));
                }

                this.$set('query', query);
                this.$notify('Saved');
            }, function (res) {
                this.$notify(res.data, 'danger');
            });
        }

    },

    components: {

        settings: require('../../components/post-edit-settings.vue')

    }

};

Vue.ready(window.DocsEdit);
