const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

/**
 * Bundles the plugin's JS entry points into assets/scripts/*.js and its
 * CSS entry points (combined via postcss-import, see postcss.config.js)
 * into assets/styles/*.css.
 */
module.exports = (env, argv) => ({
    mode: argv.mode === 'production' ? 'production' : 'development',
    devtool: argv.mode === 'production' ? false : 'source-map',
    entry: {
        frontend: './src/scripts/frontend.js',
        general: './src/scripts/backend/general.js',
        settings: './src/scripts/backend/settings.js',
        main: './src/styles/main.css',
        admin: './src/styles/backend/admin.css',
        protection: './src/styles/backend/protection.css',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'assets/scripts'),
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'],
            },
        ],
    },
    plugins: [
        new RemoveEmptyScriptsPlugin(),
        new MiniCssExtractPlugin({
            filename: '../styles/[name].css',
        }),
    ],
    optimization: {
        minimizer: ['...', new CssMinimizerPlugin()],
    },
});
