const path = require('path');

module.exports = {
    entry: {
        main: './src/js/main.js',
        admin: './src/js/admin.js'
    },
    output: {
        path: path.resolve(__dirname, 'assets/js'),
        filename: '[name].bundle.js',
        clean: true
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            },
            {
                test: /\.scss$/,
                use: ['style-loader', 'css-loader', 'sass-loader']
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.json']
    },
    devtool: 'source-map',
    optimization: {
        splitChunks: {
            chunks: 'all',
            name: false
        }
    }
}; 