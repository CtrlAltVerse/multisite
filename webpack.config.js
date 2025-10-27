const path = require('path')
const TerserPlugin = require('terser-webpack-plugin')

module.exports = {
   entry: {
      'themes/ctrl/assets/tools.min.js':
         './cavwp/wp-content/themes/ctrl/assets_dev/tools/index.js',
      'themes/ctrl/assets/main.min.js':
         './cavwp/wp-content/themes/ctrl/assets_dev/index.js',
      'plugins/cav-exclusive/assets/rewards.min.js':
         './cavwp/wp-content/plugins/cav-exclusive/assets_dev/rewards.js',
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
      filename: '[name]',
      path: path.resolve(__dirname, 'cavwp', 'wp-content'),
   },
   optimization: {
      minimize: true,
      minimizer: [new TerserPlugin()],
   },
}
