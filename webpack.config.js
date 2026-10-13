/* 构建配置：独立构建（本目录 `npm run build`）与宿主构建（宿主根目录 `npm run build:js`）共用；必须导出数组（宿主根配置对本文件调用 .map()），且下面 1)~4) 四项设置不能删 */

var path = require('path');
var { VueLoaderPlugin } = require('vue-loader');

module.exports = [

    {

        context: __dirname,

        entry: {
            "index": "./app/views/admin/index",
            "edit": "./app/views/admin/edit",
            "settings": "./app/views/admin/settings",
            "docs": "./app/views/docs"
        },

        output: {
            path: path.resolve(__dirname, './app/bundle'),
            filename: "[name].js"
        },

        /* 1) Vue 外部化到全局 Vue：否则 bundle 内会打包一份运行时副本，SFC 模板的组件解析会落到副本上而失败 */
        externals: {
            vue: 'Vue'
        },

        module: {
            rules: [
                /* 2) .vue 交给 vue-loader 17 */
                { test: /\.vue$/, loader: 'vue-loader' }
            ]
        },

        optimization: {
            /* 3) 关闭 usedExports：否则以 .vue 作入口时模板会被摇掉，组件渲染成空 */
            usedExports: false
        },

        plugins: [
            /* 4) vue-loader 17 需要它配合上面的规则 */
            new VueLoaderPlugin()
        ]

    }

];
