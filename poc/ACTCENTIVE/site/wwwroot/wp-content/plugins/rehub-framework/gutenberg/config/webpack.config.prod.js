/**
 * Webpack Configuration
 *
 * Working of a Webpack can be very simple or complex. This is an intenally simple
 * build configuration.
 *
 * Webpack basics — If you are new the Webpack here's all you need to know:
 *     1. Webpack is a module bundler. It bundles different JS modules together.
 *     2. It needs and entry point and an ouput to process file(s) and bundle them.
 *     3. By default it only understands common JavaScript but you can make it
 *        understand other formats by way of adding a Webpack loader.
 *     4. In the file below you will find an entry point, an ouput, and a babel-loader
 *        that tests all .js files excluding the ones in node_modules to process the
 *        ESNext and make it compatible with older browsers i.e. it converts the
 *        ESNext (new standards of JavaScript) into old JavaScript through a loader
 *        by Babel.
 *
 * TODO: Instructions.
 *
 * @since 1.0.0
 */

const path = require('path');
const webpack = require( 'webpack' );
const externals = require( './externals' );

// Source maps are resource heavy and can cause out of memory issue for large source files.
const shouldUseSourceMap = process.env.GENERATE_SOURCEMAP === 'true';


// Export configuration.
module.exports = {
	entry: {
		'editor': path.resolve( __dirname, '../src/blocks.js' ),
		'format': path.resolve( __dirname, '../src/formatting.js' ),
	},
	output: {
		path: path.resolve( __dirname, '../assets/js/' ),
		filename: '[name].js',
		library: '[name]',  // it assigns this module to the global (window) object
	},
	// You may want 'eval' instead if you prefer to see the compiled output in DevTools.
	devtool: shouldUseSourceMap ? 'source-map' : false,
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader',
					options: {
						// presets: ['es2015'],
						// Cache compilation results in ./node_modules/.cache/babel-loader/
						cacheDirectory: true,
						plugins: [
							["@babel/plugin-proposal-decorators", {"legacy": true}],
							['@babel/plugin-proposal-class-properties', {"loose": true}],
							'@babel/plugin-transform-destructuring',
							'@babel/plugin-proposal-object-rest-spread',
							'@babel/plugin-transform-modules-commonjs',
							["@babel/plugin-proposal-private-methods", {"loose": true}],
							["@babel/plugin-transform-classes", {"loose": true}],
							"transform-function-bind",
							['@babel/plugin-transform-react-jsx', {pragma: 'wp.element.createElement'}]
						]
					}
				}
			},
			{
				test: /\.svg$/,
				use: ['@svgr/webpack'], // Settings are in .svgrrc
			},
			{
				test: /\.(png|jpg|gif)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							outputPath: 'images', // Dump images in dist/images.
							publicPath: 'dist/images', // URLs point to dist/images.
							regExp: /\/([^\/]+)\/([^\/]+)\/images\/(.+)\.(.*)?$/, // Gather strings for the output filename.
							name: '[1]-[2]-[3].[hash:hex:7].[ext]', // Filename e.g. block-accordion-basic.1b659fc.png
						},
					},
				],
			},
		],
	},

	// Add plugins.
	plugins: [
		// Minify the code.
		new webpack.optimize.UglifyJsPlugin( {
			compress: {
				warnings: false,
				// Disabled because of an issue with Uglify breaking seemingly valid code:
				// https://github.com/facebookincubator/create-react-app/issues/2376
				// Pending further investigation:
				// https://github.com/mishoo/UglifyJS2/issues/2011
				comparisons: false,
			},
			mangle: {
				safari10: true,
				except: ['__', '_n', '_x', '_nx' ],
			},
			output: {
				comments: false,
				// Turned on because emoji and regex is not minified properly using default
				// https://github.com/facebookincubator/create-react-app/issues/2488
				ascii_only: true,
			},
			sourceMap: shouldUseSourceMap,
		} ),
	],
	stats: {
		all: false,
		assets: true,
		colors: true,
		errors: true,
		performance: true,
		timings: true,
		warnings: true,
	},
	// stats: 'errors-only',
	// Add externals.
	externals: externals,
};
