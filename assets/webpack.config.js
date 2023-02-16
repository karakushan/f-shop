const {VueLoaderPlugin} = require("vue-loader");
const htmlWebpackPlugin = require("html-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const {CleanWebpackPlugin} = require("clean-webpack-plugin");
const autoprefixer = require("autoprefixer");
const path = require("path");

module.exports = {
    watch: true,
    entry: {
        "fs-admin": "../assets/js/src/fs-admin.js",
        "vue-admin": "../assets/js/src/vue-admin.js"
    },
    output: {
        filename: "[name].js",
        path: path.resolve(__dirname, "js"),
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                },
            },
            {
                test: /\.vue$/,
                loader: "vue-loader",
            },
            {
                test: /\.s?css$/,
                use: [
                    "style-loader",
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    {
                        loader: "postcss-loader",
                        options: {
                            plugins: () => [autoprefixer()],
                        },
                    },
                    "sass-loader",
                ],
            },
            {
                test: /\.(eot|ttf|woff|woff2)(\?\S*)?$/,
                loader: "file-loader",
            },
            {
                test: /\.(png|jpe?g|gif|webm|mp4|svg)$/,
                loader: "file-loader",
                options: {
                    name: "[name][contenthash:8].[ext]",
                    outputPath: "assets/img",
                    esModule: false,
                },
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        // new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: "[name].css",
            chunkFilename: "[name].css",
        }),
        // new htmlWebpackPlugin({
        //     template: path.resolve(__dirname, "./", "index.html"),
        //     favicon: "./public/favicon.ico",
        // }),
    ],
    resolve: {
        alias: {
            vue$: "vue/dist/vue.esm.js",
        },
        extensions: ["*", ".js", ".vue", ".json"],
    },
    // optimization: {
    //     moduleIds: "hashed",
    //     runtimeChunk: "single",
    //     splitChunks: {
    //         cacheGroups: {
    //             vendor: {
    //                 test: /[\\/]node_modules[\\/]/,
    //                 name: "vendors",
    //                 priority: -10,
    //                 chunks: "all",
    //             },
    //         },
    //     },
    // },
    devServer: {
        historyApiFallback: true,
    },
};