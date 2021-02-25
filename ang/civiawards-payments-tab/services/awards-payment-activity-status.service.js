(function (_, angular) {
  var module = angular.module('civiawards-payments-tab');

  module.service('AwardsPaymentActivityStatus', AwardsPaymentActivityStatus);

  /**
   * Awards Payment Activity Status service
   *
   * @param {object} ActivityStatus activity status service
   */
  function AwardsPaymentActivityStatus (ActivityStatus) {
    this.isDeleteVisible = function (activity) {
      var activityStatus = activity.status_name;
      var notEditableStatuses = [
        'paid_complete',
        'failed_incomplete',
        'exported_complete'
      ];

      return !_.includes(notEditableStatuses, activityStatus);
    };

    this.isEditVisible = function (activity) {
      var activityStatus = activity.status_name;
      var notEditableStatuses = [
        'exported_complete'
      ];

      return !_.includes(notEditableStatuses, activityStatus);
    };
  }
})(CRM._, angular);
