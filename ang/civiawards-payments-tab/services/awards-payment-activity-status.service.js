(function (angular) {
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
        ActivityStatus.findByName('paid_complete').name,
        ActivityStatus.findByName('failed_incomplete').name,
        ActivityStatus.findByName('exported_complete').name
      ];

      return notEditableStatuses.indexOf(activityStatus) === -1;
    };
  }
})(angular);
