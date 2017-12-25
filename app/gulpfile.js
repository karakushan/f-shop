var gulp = require('gulp'),
    uglify = require('gulp-uglify'),
    pump = require('pump'),
    concat = require('gulp-concat');

// объединение скриптов
gulp.task('concat', function () {
    return gulp.src(['./js/*.js', '!./js/f-shop.js'])
        .pipe(concat('f-shop.js'))
        .pipe(gulp.dest('./js'));
});

// минификация скриптов
gulp.task('compress', function (cb) {
    pump([
            gulp.src('./js/f-shop.js'),
            uglify(),
            gulp.dest('../assets/js/')
        ],
        cb
    );
});

// Отслеживание изменений и перекомпоновка
gulp.task('watch', function () {
    gulp.watch('./js/*.js', ['concat']);
    gulp.watch('./js/f-shop.js', ['compress']);
});

// этот таск запускается автоматом при вводе команды gulp
gulp.task('default', ['concat', 'compress', 'watch']);
