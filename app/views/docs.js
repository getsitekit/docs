Vue.ready(function () {
    Array.prototype.forEach.call(document.querySelectorAll('.js-docs-meta'), function (el) {
        new Vue({}).$mount(el);
    });
});
