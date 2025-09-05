const path = require('path')
const TerserPlugin = require('terser-webpack-plugin')

module.exports = {
   entry: {
      ctrl: './cavwp/wp-content/themes/ctrl/assets_dev/index.js',
   },
   module: {
      rules: [
         {
            test: /\.tsx?$/,
            use: 'ts-loader',
            exclude: /node_modules/,
         },
      ],
   },
   resolve: {
      extensions: ['.tsx', '.ts', '.js'],
   },
   output: {
      filename: '[name]/assets/main.min.js',
      path: path.resolve(__dirname, 'cavwp', 'wp-content', 'themes'),
   },
   optimization: {
      minimize: true,
      minimizer: [new TerserPlugin()],
   },
}
