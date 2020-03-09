'use strict';

var gulp = require('gulp');

var sassTask = require('./gulp-tasks/sass.js');
var testTask = require('./gulp-tasks/karma-unit-test.js');

/**
 * Compiles scss into CSS counterpart
 */
gulp.task('sass', sassTask);

/**
 * Runs Karma unit tests
 */
gulp.task('test', testTask);

/**
 * Default tasks
 */
gulp.task('default', gulp.parallel('sass'));
