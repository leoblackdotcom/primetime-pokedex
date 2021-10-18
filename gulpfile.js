const gulp = require('gulp');
const stylus = require('gulp-stylus');
const postcss = require('gulp-postcss');
const browserSync = require('browser-sync').create();
const sourcemaps = require('gulp-sourcemaps');
const changed = require('gulp-changed');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');

var cssDest = './dist/css';

gulp.task('stylus', function(){
	return gulp.src('./src/styl/pokedex.styl')
		.pipe(sourcemaps.init())
        .pipe(stylus())
		//.pipe(changed(cssDest))
        .pipe(postcss([
            require('autoprefixer'), 
            require('postcss-combine-media-query'), 
            require('postcss-combine-duplicated-selectors')
        ]))
		//.pipe(changed(cssDest))
		.pipe(sourcemaps.write())
        .pipe(gulp.dest(cssDest))
        .pipe(browserSync.stream())
});

gulp.task('browser-sync', function() {
  browserSync.init({
    proxy: "https://primetime-pokemon.local/"
  });
});

gulp.task('watch', function(){
    gulp.watch('src/styl/**/*.styl', gulp.series('stylus'));
    gulp.watch('**/*.php').on('change', browserSync.reload);
    gulp.watch('../../themes/matthew-child/**/*.php').on('change', browserSync.reload);
});

gulp.task('default', gulp.parallel('stylus','browser-sync','watch'));