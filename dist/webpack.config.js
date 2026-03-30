"use strict";
var path = require('path');
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
                use: ['style-loader', 'css-loader', 'sass-loader'],
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
    resolve: {
        extensions: ['.tsx', '.ts', '.js'], // Permet l'import sans extension
    },
    mode: 'development',
};
//# sourceMappingURL=webpack.config.js.map