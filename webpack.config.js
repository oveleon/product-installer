const path = require('path');

module.exports = {
    mode: 'development', //production
    entry: './public/scripts/index.js',
    output: {
        path: path.resolve(__dirname, 'public/build'),
        filename: 'main.js',
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                use: 'ts-loader'
            },
            {
                test: /\.(scss|css)$/,
                use: ['style-loader', 'css-loader', 'sass-loader'],
            }
        ]
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js', '.scss'],
    },
};
