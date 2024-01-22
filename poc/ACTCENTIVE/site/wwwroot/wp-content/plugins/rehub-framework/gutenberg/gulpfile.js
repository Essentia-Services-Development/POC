/**
 * These tasks are for building styles.
 *
 */

const autoprefixer = require('autoprefixer'),
      concat       = require('gulp-concat'),
      cssnano      = require('cssnano'),
      gulp         = require('gulp'),
      mqpacker     = require('css-mqpacker'),
      path         = require('path'),
      postcss      = require('gulp-postcss'),
      rename       = require('gulp-rename'),
      sass         = require('gulp-sass'),
      zip          = require('gulp-zip');

// These files are the ones which will be included in the `package` task.
const buildInclude = [
	path.resolve(__dirname, './*.+(txt|php)'), // All files in the root.
	path.resolve(__dirname, './src/**/*.php'), // Only PHP files in our source files, others will be compiled into dist.
	path.resolve(__dirname, './dist/**'),
	path.resolve(__dirname, './freemius/**'),
	path.resolve(__dirname, './images/**'),
	path.resolve(__dirname, './src/welcome/images/**'), // Welcome screen / settings images.
	'!' + path.resolve(__dirname, './dist/deprecation-tests.json'),
	'!' + path.resolve(__dirname, './dist/videos/**'), // Help tooltip videos.
];

const postCSSOptions = [
	autoprefixer({remove: false}),
	mqpacker({sort: true}), // Combine media query rules.
	cssnano(), // Minify.
];

const sassOptions = {
	includePaths: [
		path.resolve(__dirname, './node_modules/'),
		path.resolve(__dirname, './src/')
	]
};

// Gets all directories recursively.
const getDirectories = function (dir, filelist) {
	const fs = require('fs');
	const files = fs.readdirSync(dir);
	filelist = filelist || [];

	if (fs.statSync(dir).isDirectory()) {
		filelist.push(dir)
	}
	files.forEach(function (file) {
		if (fs.statSync(path.join(dir, file)).isDirectory()) {
			filelist = getDirectories(path.join(dir, file), filelist)
		}
	});
	return filelist
};

gulp.task('style-editor', function () {
	return gulp.src([path.resolve(__dirname, './src/common.scss'), path.resolve(__dirname, './src/**/editor.scss')])
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(concat('editor.css'))
		.pipe(postcss(postCSSOptions))
		.pipe(gulp.dest('assets/css/'))
});

gulp.task('style-editor-rtl', function () {
	return gulp.src([
		path.resolve(__dirname, './src/general.scss'),
		path.resolve(__dirname, './src/**/editor-rtl.scss')
	])
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(concat('editor-rtl.css'))
		.pipe(postcss(postCSSOptions))
		.pipe(gulp.dest('assets/css/'))
});

gulp.task('style', function () {
	return gulp.src([path.resolve(__dirname, './src/general.scss'), path.resolve(__dirname, './src/**/style.scss')])
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(postcss(postCSSOptions))
		.pipe(gulp.dest('assets/css/'))
});

gulp.task('style-rtl', function () {
	return gulp.src([path.resolve(__dirname, './src/general.scss'), path.resolve(__dirname, './src/**/style-rtl.scss')])
		.pipe(sass(sassOptions).on('error', sass.logError))
		.pipe(concat('backend-rtl.css'))
		.pipe(postcss(postCSSOptions))
		.pipe(gulp.dest('assets/css/'))
});

gulp.task('build-process', gulp.parallel('style', 'style-rtl', 'style-editor', 'style-editor-rtl'));
gulp.task('build', gulp.series('build-process'));

const watchFuncs = () => {
	gulp.watch(
		[path.resolve(__dirname, './src/**/*.scss')],
		gulp.parallel(['style', 'style-rtl', 'style-editor', 'style-editor-rtl'])
	);
};

gulp.task('watch', gulp.series('build-process', watchFuncs));

module.exports = {
	postCSSOptions,
	sassOptions,
	watchFuncs,
};

