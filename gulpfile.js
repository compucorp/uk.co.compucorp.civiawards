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
gulp.task('test', (done) => testTask(done, { singleRun: true }));
gulp.task('test:watch', (done) => testTask(done, { singleRun: false }));

/**
 * Default tasks
 */
gulp.task('default', gulp.parallel('sass'));
