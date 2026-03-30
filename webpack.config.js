const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: './assets/app.ts', // Nouveau point d'entrée en TypeScript
    output: {
        filename: 'app.bundle.js',
        path: path.resolve(__dirname, 'public/build'),
        publicPath: '/build/',
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/, // Ajoute le support de .ts et .tsx
                use: 'ts-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.scss$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
            },
            {
                test: /\.(png|jpe?g|gif|svg)$/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[hash].[ext]',
                            outputPath: 'images/',
                        },
                    },
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'app.bundle.css',
        }),
    ],
    resolve: {
        extensions: ['.tsx', '.ts', '.js'], // Permet l'import sans extension
    },
    mode: 'development',
    devtool: 'source-map',
};