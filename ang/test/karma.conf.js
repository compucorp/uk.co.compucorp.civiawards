var cv = require('civicrm-cv')({ mode: 'sync' });

module.exports = function (config) {
  var civicrmPath = cv("path -d '[civicrm.root]'")[0].value;
  var extPath = cv('path -x uk.co.compucorp.civiawards')[0].value;

  config.set({
    basePath: civicrmPath,
    frameworks: ['jasmine'],
    files: [
      // the global dependencies
      'bower_components/jquery/dist/jquery.min.js',
      'bower_components/jquery-ui/jquery-ui.js',
      'bower_components/lodash-compat/lodash.min.js',
      'bower_components/select2/select2.min.js',
      'bower_components/jquery-validation/dist/jquery.validate.min.js',
      'packages/jquery/plugins/jquery.blockUI.js',
      'js/Common.js',

      'bower_components/angular/angular.min.js',
      'bower_components/angular-mocks/angular-mocks.js',
      'bower_components/angular-route/angular-route.min.js',

      // Global variables that need to be accessible in the test environment
      extPath + '/ang/test/global.js',

      // angular templates
      extPath + '/ang/civiawards/**/*.html',

      // Source Files
      extPath + '/ang/civiawards.js',
      { pattern: extPath + '/ang/civiawards/**/*.js' },

      // Spec files
      { pattern: extPath + '/ang/test/mocks/modules.mock.js' },
      { pattern: extPath + '/ang/test/mocks/**/*.js' },
      { pattern: extPath + '/ang/test/civiawards/**/*.js' }
    ],
    exclude: [
    ],
    // Used to transform angular templates in JS strings
    preprocessors: (function (obj) {
      obj[extPath + '/ang/civiawards/**/*.html'] = ['ng-html2js'];
      return obj;
    })({}),
    ngHtml2JsPreprocessor: {
      stripPrefix: extPath + '/ang',
      prependPrefix: '~',
      moduleName: 'civiawards.templates'
    },
    reporters: ['progress'],
    // web server port
    port: 9876,
    colors: true,
    logLevel: config.LOG_INFO,
    autoWatch: true,
    browsers: ['ChromeHeadless'],
    customLaunchers: {
      ChromeHeadless: {
        base: 'Chrome',
        flags: [
          '--headless',
          '--disable-gpu',
          // Without a remote debugging port, Google Chrome exits immediately.
          '--remote-debugging-port=9222'
        ]
      }
    }
  });
};
