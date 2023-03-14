// eslint-disable-next-line import/no-extraneous-dependencies
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');


const path = require('path');

const mode = process.env.NODE_ENV || 'development';
const prod = mode === 'production';

module.exports = {
  entry: {
    'build/bundle': ['./modules/project_browser/sveltejs/src/main.js'],
  },
  resolve: {
    alias: {
      svelte: path.dirname(require.resolve('svelte/package.json')),
    },
    extensions: ['.mjs', '.js', '.svelte'],
    mainFields: ['svelte', 'browser', 'module', 'main'],
    conditionNames: ['svelte'],
  },
  output: {
    path: path.join(__dirname, '/public'),
    filename: '[name].js',
    chunkFilename: '[name].[id].js',
  },
  module: {
    rules: [
      {
        test: /\.svelte$/,
        use: {
          loader: 'svelte-loader',
          options: {
            compilerOptions: {
              dev: !prod,
              cssHash: ({ hash, css, name, filename }) =>
                // Strip all line breaks and whitespace from the CSS string used to
                // calculate hash.
                `pb-${hash(
                  css ? css.replaceAll(/(\r\n|\n|\r|\s)/gm, '') : '',
                )}`,
            },
            emitCss: true,
            hotReload: !prod,
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader,
            options: {
              publicPath: path.join(__dirname, '/public/build'),
            },
          },
          'css-loader',
        ],
      },
      {
        // required to prevent errors from Svelte on Webpack 5+
        test: /node_modules\/svelte\/.*\.mjs$/,
        resolve: {
          fullySpecified: false,
        },
      },
    ],
  },
  mode,
  plugins: [
    new MiniCssExtractPlugin({ filename: 'build/bundle.css' }),
    new CssMinimizerPlugin()
  ],
  devtool: prod ? false : 'source-map',
  optimization: {
    minimize: true,
    minimizer: [
      new CssMinimizerPlugin({
        minify: CssMinimizerPlugin.cssnanoMinify,
      }),
      new TerserPlugin({
        extractComments: false,
      }),
    ],
  },
};
