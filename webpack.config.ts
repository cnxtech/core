import { Chain, initBase, initLoaders, initPlugins } from '@radic/build-tools-webpack'
import { resolve } from 'path';
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import Copy from 'copy-webpack-plugin';
import * as dotenv from 'dotenv'

const ForkTsChecker = require('fork-ts-checker-webpack-plugin');

const chain: Chain      = initBase({
    mode         : process.env.NODE_ENV as any,
    entryFileName: 'main.ts',
    sourceDir    : resolve(__dirname, 'theme'),
    outputDir    : resolve(__dirname, process.env.NODE_ENV === 'development' ? 'dev' : 'prod')
});
const { isDev, isProd } = chain;

chain
    .devtool(isDev ? 'cheap-module-source-map' : false)
    .resolve.alias.merge(
    {
        'lodash-es$': 'lodash',
        'lodash-es' : 'lodash',
        'jquery$'   : 'jquery/src/jquery.js'
    }).end()
    .modules.add(chain.srcPath());

chain.output.devtoolModuleFilenameTemplate(info => 'file://' + resolve(info.absoluteResourcePath).replace(/\\/g, '/'))

const loaders = initLoaders(chain);
let tsconfig  = resolve(__dirname, 'tsconfig.webpack.json');

loaders.addTypescriptLoader(chain, tsconfig);
// loaders.addBabelToTypescriptLoader(chain);
loaders.addJavascriptLoader(chain);
loaders.addTypescriptImportFactories(chain, [
    { style: false, libraryName: 'lodash', libraryDirectory: null, camel2DashComponentName: false },
    { style: false, libraryName: 'lodash-es', libraryDirectory: null, camel2DashComponentName: false }
])


chain.addLoader('fonts', 'file-loader', {
    name      : '[name].[ext]',
    publicPath: `/fonts/`,
    outputPath: 'fonts/'
}).test(/\.*\.(woff2?|woff|eot|ttf|otf)(\?.*)?$/)
chain.addLoader('images', 'file-loader', {
    name      : '[name].[ext]',
    publicPath: `/img/`,
    outputPath: 'img/'
}).test(/\.*\.(png|jpe?g|gif|svg)(\?.*)?$/)


const plugins = initPlugins(chain);
plugins.addWriteFilePlugin(chain)
plugins.addDefinePlugin(chain, {
    ENV: dotenv.load({ path: resolve('.env') }).parsed
})
plugins.addCleanPlugin(chain, [ 'js/', 'css/', '*.hot-update.js', '*.hot-update.js.map', '*.hot-update.json', 'assets/' ])
plugins.addCliPlugins(chain)
plugins.addCssExtractPlugins(chain, isProd)
plugins.addOptimizerPlugins(chain, isProd)
plugins.addAnalyzerPlugins(chain, true)

chain.plugin('copy').use(Copy, [ [
        { from: chain.srcPath('assets'), to: chain.outPath('assets') }
    ] ]
)

chain.optimization.namedModules(true);
chain.optimization.namedChunks(true);
chain.optimization.runtimeChunk(true)
chain.optimization.minimize(false);
chain.performance.hints(false);
chain.optimization.splitChunks({ name: true })

if ( isDev ) {
    chain.optimization.runtimeChunk('single')
} else {
    chain.optimization.minimize(true);
    chain.performance.hints(false);
}

chain.onToConfig(config => {

    config.module.rules.push(...[ {
        test   : /\.module.css$/,
        include: chain.srcPath(),
        use    : [
            isDev ? { loader: 'style-loader', options: { sourceMap: true } } : MiniCssExtractPlugin.loader,
            { loader: 'css-loader', options: { importLoaders: 1, sourceMap: isDev, modules: true, localIdentName: '[name]__[local]' } },
            { loader: 'postcss-loader', options: { sourceMap: isDev, plugins: [ require('autoprefixer'), require('cssnext'), require('postcss-nested') ] } }
        ]
    }, {
        test   : /\.css$/,
        exclude: [ /\.module.css$/ ],
        use    : [
            isDev ? { loader: 'style-loader', options: { sourceMap: true } } : MiniCssExtractPlugin.loader,
            { loader: 'css-loader', options: { importLoaders: 1, sourceMap: isDev } },
            { loader: 'postcss-loader', options: { sourceMap: isDev, plugins: [ require('autoprefixer'), require('cssnext'), require('postcss-nested') ] } }
        ]
    }, {
        test: /\.scss$/,
        use : [
            isDev ? { loader: 'style-loader', options: { sourceMap: true } } : MiniCssExtractPlugin.loader,
            { loader: 'css-loader', options: { importLoaders: 2, sourceMap: isDev, camelCase: false } },
            { loader: 'postcss-loader', options: { sourceMap: isDev, plugins: [ require('autoprefixer'), require('cssnext') ] } },
            { loader: 'sass-loader', options: { sourceMap: isDev, outputStyle: 'expanded' } }
        ]
    }, {
        test: /\.less$/,
        use : [
            isDev ? { loader: 'style-loader', options: { sourceMap: true } } : MiniCssExtractPlugin.loader,
            { loader: 'css-loader', options: { importLoaders: 2, sourceMap: isDev } },
            { loader: 'postcss-loader', options: { sourceMap: isDev, plugins: [ require('autoprefixer'), require('cssnext') ] } },
            { loader: 'less-loader', options: { javascriptEnabled: true, sourceMap: isDev } }
        ]
    } ])
    return config;
})


const config = chain.toConfig();
config.plugins.push(new ForkTsChecker({
    async: true,
    watch: chain.srcPath(),
    tsconfig
}));

let a = 'a';
export { chain, config }
