const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const autoprefixer = require("autoprefixer");
const path = require("path");

module.exports = {
    watch: true,
    entry: {
        "fs-admin": "../assets/src/fs-admin.js",
        "f-shop": "../assets/src/f-shop.js",
        "fs-frontend": "../assets/src/fs-frontend.js",
        "fs-backend": "../assets/src/fs-backend.js",
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
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false,
                            importLoaders: 1,
                            sourceMap: true
                        }
                    }
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: false,
                            importLoaders: 2,
                            sourceMap: true
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {sourceMap: true}
                    }
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
        new MiniCssExtractPlugin({
            filename: "../css/[name].css",
            chunkFilename: "[name].css"
        })
    ],
    resolve: {
        extensions: ["*", ".js", ".json"],
    }
};