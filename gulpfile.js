'use strict';

var gulp = require('gulp');

var sassTask = require('./gulp-tasks/sass.js');

/**
 * Compiles scss into CSS counterpart
 */
gulp.task('sass', sassTask);

/**
 * Default tasks
 */
gulp.task('default', gulp.parallel('sass'));
