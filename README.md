# Docs

给站点加一套「文档」系统：后台按「分类 → 文档」维护内容，前台按分类树浏览，并自动生成正文内的标题锚点目录。

## 功能

- **后台列表**：分类增删改 + 拖拽排序；文档表格支持搜索、状态/分类筛选、按标题排序、分页，以及批量发布 / 取消发布 / 删除；文档同样支持拖拽排序
- **后台编辑**：标题、Markdown 正文、别名（slug）、状态、分类、日期、源文件编辑链接
- **前台**：左栏按分类列出文档（当前项高亮）、中栏正文、右栏正文内 `h1/h2` 锚点目录
- **访问控制**：分类可绑定角色（`roles` 为空表示所有人可见），仅有对应角色的用户能看到该分类及其文档
- **固定链接**：数字型 `/docs/123`、名称 `/docs/sample-post`、日期型 `/docs/2026/10/13/sample-post`、自定义占位符

## 环境要求

- PHP >= 8.3
- 宿主需提供：
  - Pagekit 框架（`Pagekit\*`，含路由/ORM/事件）
  - Sitekit UI（`sk-*` 样式与 `SK.*` 组件：sortable、tab、modal、tooltip 等）
  - Vue 3 + 兼容层（`Vue.ready`、`$trans`/`$transChoice`、`$resource`/`$http`、`$relativeDate`、`data-sk-*` 指令）

> 本扩展是按上述宿主环境编写的，装到官方原版 Pagekit 上大概率不兼容。

## 安装

1. **后台上传**：把扩展目录**里面的内容**打成 zip（`composer.json` 必须在压缩包**最外层**），在「系统 → 拓展」上传并安装
2. **手动放置**：解压到 `packages/sitekit/docs/`，再到「系统 → 拓展」启用

启用时会执行 `scripts.php`：建表（`@docs_post`、`@docs_category`）并写入一批示例文档（幂等，按 slug 跳过已存在的）。

## 目录结构

```
composer.json              扩展元数据（type: pagekit-extension）
index.php                  模块清单：路由 / 菜单 / 节点类型 / 权限 / 配置 / 事件
scripts.php                安装与卸载脚本（建表 + 示例数据）
src/
  Controller/              后台页面、后台 API、前台页面
  Model/                   Category / Docs 实体
  UrlResolver.php          固定链接（permalink）解析
  Event/RouteListener.php  路由别名与缓存失效
views/
  admin/                   后台模板（列表 / 编辑 / 设置）
  index.php                前台模板
app/
  views/                   入口 JS（admin/index、admin/edit、admin/settings、docs）
  components/              分段组件（Vue SFC）
  bundle/                  构建产物（需提交）
assets/docs.css            前台样式补充
languages/                 en_US / zh_CN 语言包
```

## 构建

两种方式都可用，共用同一份 `webpack.config.js`：

```bash
# 方式一：在本扩展目录独立构建（仓库单独使用时）
npm install
npm run build        # = webpack --mode production，产物写入 app/bundle/
npm run dev          # = webpack --mode development

# 方式二：在宿主 CMS 项目根目录（会一并构建本扩展）
npm install
npm run build:js
```

`webpack.config.js` 里有两条硬性约束，改的时候别去掉：

1. **必须导出数组**（`module.exports = [ {...} ]`）——宿主根配置会对子配置调用 `.map()`，导出对象会直接报 `TypeError: require(...).map is not a function`；
2. **必须自带 4 条设置**：`externals: {vue: 'Vue'}`（Vue 外部化到全局 `Vue`）、`.vue` 交给 vue-loader 17、`optimization.usedExports = false`（否则 `.vue` 入口的模板会被摇掉）、注入 `VueLoaderPlugin`。宿主构建时那层归一化只补 `context`/`output`、追加 `externals`、强制 vue-loader 17、关闭 `usedExports`，独立构建时全靠这些设置。

⚠️ `app/bundle/` 是构建产物，但**必须提交**：模板直接引用这些文件，用户安装后不会再跑构建。

## 许可

MIT，见 [LICENSE](LICENSE)。
