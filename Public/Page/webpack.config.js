const path = require('path');
const webpack = require('webpack');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

var argv = require('yargs').argv;
var argvs = argv._ ;

var entry = {
    home: './src/home/index.js'
}

var webpackConfig = {
    resolve:{
        alias : {
            COMMON : path.resolve(__dirname,'../common')
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
                use: ['style-loader', 'css-loader','postcss-loader']
            }, {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: ['css-loader' ,'sass-loader']
                })
                // use: ['style-loader','css-loader' ,'sass-loader']
            }
        ]
    },

    plugins: [
        new ExtractTextPlugin('[name].min.css'),
        require('autoprefixer')
    ]
};

if(argvs.length > 0){

    argvsArr = argvs[0].split('/');
    var key = argvsArr[2];
    webpackConfig.entry = {};
    webpackConfig.entry[key] = argvs[0];

    // webpackConfig.output.path = path.resolve(__dirname, 'dist');

}

module.exports = webpackConfig;