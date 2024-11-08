const {src, dest, watch, series} = require('gulp');

const sass = require('gulp-sass')(require('sass'));
const prefix = require('gulp-autoprefixer');
const minify = require('gulp-clean-css');
const rename = require('gulp-rename');

function compilerSass ()
{
    return src([
      './styles/styles.scss'
    ])
      .pipe(sass())
      .pipe(prefix())
      .pipe(minify())
      .pipe(rename('styles.min.css'))
      .pipe(dest('./dist/css'));
}

function watchTask(){
    watch("./styles/**/*.scss", compilerSass);
}

exports.default = series(
    compilerSass,
    watchTask
);
