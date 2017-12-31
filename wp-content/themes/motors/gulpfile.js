var gulp = require('gulp');
var sass = require('gulp-sass');
var autoprefixer = require('gulp-autoprefixer');
var livereload = require('gulp-livereload');

gulp.task('styles', function() {
   gulp.src('./../motorswp/wp-content/themes/motors/assets/css/rental/**/*.scss')
       .pipe(sass().on('error', sass.logError))
       .pipe(autoprefixer())
       .pipe(gulp.dest('../motorswp/wp-content/themes/motors/assets/css/rental/css'))
       .pipe(livereload());
});

gulp.task('watch', function() {
   livereload.listen();
   gulp.watch('./../motorswp/wp-content/themes/motors/assets/css/rental/**/*.scss', ['styles']);
});

gulp.task('default', ['styles', 'watch']);