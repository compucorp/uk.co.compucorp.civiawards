(function (angular) {
  var module = angular.module('civiawards');

  module.config(activityFormsConfiguration);

  /**
   * Award activity forms configuration.
   *
   * @param {object} ActivityFormsProvider the activity forms provider.
   */
  function activityFormsConfiguration (ActivityFormsProvider) {
    var activityFormConfigs = [
      {
        name: 'ReviewActivityForm',
        weight: -1
      },
      {
        name: 'PaymentsActivityForm',
        weight: -2
      }
    ];

    ActivityFormsProvider.addActivityForms(activityFormConfigs);
  }
})(angular);
