var cv = require('civicrm-cv')({ mode: 'sync' });

module.exports = function (config) {
  var civicrmPath = cv("path -d '[civicrm.root]'")[0].value;
  var civicasePath = cv('path -x uk.co.compucorp.civicase')[0].value;
  var extPath = cv('path -x uk.co.compucorp.civiawards')[0].value;

  config.set({
    basePath: civicrmPath,
    frameworks: ['jasmine', 'jasmine-diff'],
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

      civicasePath + '/packages/moment.min.js',

      // Global variables that need to be accessible in the test environment
      civicasePath + '/ang/test/global.js',
      extPath + '/ang/test/global.js',

      // angular templates
      extPath + '/ang/civiawards/**/*.html',

      // Source Files
      civicasePath + '/ang/civicase-base.js',
      { pattern: civicasePath + '/ang/civicase-base/**/*.js' },

      civicasePath + '/ang/civicase.js',
      { pattern: civicasePath + '/ang/civicase/**/*.js' },

      extPath + '/ang/civiawards-base.js',
      { pattern: extPath + '/ang/civiawards-base/**/*.js' },

      extPath + '/ang/civiawards.js',
      { pattern: extPath + '/ang/civiawards/**/*.js' },

      extPath + '/ang/civiawards-workflow.js',
      { pattern: extPath + '/ang/civiawards-workflow/**/*.js' },

      // Spec files
      { pattern: civicasePath + '/ang/test/mocks/modules.mock.js' },
      { pattern: civicasePath + '/ang/test/mocks/**/*.js' },
      { pattern: extPath + '/ang/test/mocks/modules.mock.js' },
      { pattern: extPath + '/ang/test/mocks/**/*.js' },
      { pattern: extPath + '/ang/test/civiawards-base/**/*.js' },
      { pattern: extPath + '/ang/test/civiawards/**/*.js' },
      { pattern: extPath + '/ang/test/civiawards-workflow/**/*.js' }
    ],
    exclude: [
    ],
    // Used to transform angular templates in JS strings
    preprocessors: (function (obj) {
      obj[extPath + '/ang/civiawards/**/*.html'] = ['ng-html2js'];
      return obj;
    })({}),
    ngHtml2JsPreprocessor: {
      stripPrefix: '.*uk.co.compucorp.civiawards/ang',
      prependPrefix: '~',
      moduleName: 'civiawards.templates'
    },
    reporters: ['progress'],
    // web server port
    port: 9876,
    colors: true,
    logLevel: config.LOG_INFO,
    autoWatch: true,
    browsers: ['ChromeHeadlessBrowser'],
    customLaunchers: {
      ChromeHeadlessBrowser: {
        base: 'ChromeHeadless',
        flags: [
          '--no-sandbox',
          '--disable-dev-shm-usage'
        ]
      }
    }
  });
};
