const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

var argv = require('yargs').argv;
var argvs = argv._;

var entry = {
    d_route: './src/d_route/index.js',
    d_ticket: './src/d_ticket/index.js',
    home: './src/home/index.js',
    login: './src/login/index.js',
    order: './src/order/index.js',
    p_route: './src/p_route/index.js',
    p_ticket: './src/p_ticket/index.js',
    register: './src/register/index.js',
    route_detail: './src/route_detail/index.js',
    route_pay: './src/route_pay/index.js',
    s_all: './src/s_all/index.js',
    search: './src/search/index.js',
    ticket_detail: './src/ticket_detail/index.js',
    ticket_pay: './src/ticket_pay/index.js',
}

var webpackConfig = {
    devtool: 'inline-source-map',
    resolve: {
        alias: {
            COMMON: path.resolve(__dirname, '../common')
        }
    },
    entry: entry,
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'dist')
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader', 'postcss-loader']
            }, {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: ['css-loader', 'autoprefixer-loader', 'sass-loader']
                })
            }
        ]
    },

    plugins: [
        new ExtractTextPlugin('[name].min.css'),
        require('autoprefixer')
    ]
};

if (argvs.length > 0) {

    argvsArr = argvs[0].split('/');
    var key = argvsArr[2];
    webpackConfig.entry = {};
    webpackConfig.entry[key] = argvs[0];

    // webpackConfig.output.path = path.resolve(__dirname, 'dist');

}

module.exports = webpackConfig;