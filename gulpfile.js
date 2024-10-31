// Include gulp
var fs = require('fs');
var path = require('path');
var gulp = require('gulp');
// Include Our Plugins
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
let cleanCSS = require('gulp-clean-css');
var plumber = require('gulp-plumber');
var autoprefixer  = require('gulp-autoprefixer');

var onError = function (err) {
	console.log(err);
};

function get_folders(dir) {
	return fs.readdirSync(dir)
		.filter(function (file) {
			return fs.statSync(path.join(dir, file)).isDirectory();
		});
}

// Lint Task
gulp.task('lint', gulp.series(function (done) {
	return gulp.src('assets/src/js/**/*.js')
		.pipe(jshint({"esversion": 6}))
		.pipe(jshint.reporter('default'));
}));

// Compile Our Sass
gulp.task('sass', gulp.series(function (done) {
	return gulp.src('assets/src/scss/*.scss')
		//.pipe(sourcemaps.init())
		.pipe(plumber({
			errorHandler: onError
		}))
		.pipe(sass())
		.pipe(autoprefixer({
			cascade: true
		}))
		.pipe(cleanCSS())
		//.pipe(sourcemaps.write('./assets/maps'))
		.pipe(gulp.dest('./assets/css/'))
}));

/*
* js groups
*/
gulp.task('js-groups', gulp.series(function (done) {
	var scripts_path = './assets/src/js/groups';
	var folders = get_folders(scripts_path);
	var tasks = folders.map(function (folder) {
		return gulp.src(path.join(scripts_path, folder, '/**/*.js'))
			.pipe(concat(folder + '.js'))
			.pipe(gulp.dest('assets/js/'))
			.pipe(rename(folder + '.min.js'))
			.pipe(uglify())
			.pipe(gulp.dest('assets/js'));
	});
	done();
}));

// Watch Files For Changes
gulp.task('watch', function (done) {
	gulp.watch('assets/src/js/groups/**/*.js', gulp.series(['lint', 'js-groups']));
	gulp.watch('assets/src/scss/**/*.scss', gulp.series(['sass']));
	done();
});

// Default Task
gulp.task('default', gulp.series(['lint', 'sass','js-groups', 'watch']));
