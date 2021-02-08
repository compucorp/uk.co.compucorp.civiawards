(function (angular, settings) {
  var module = angular.module('civiawards-payments-tab');

  module.constant('paymentTypes', settings.payment_types);
})(angular, CRM['civiawards-payments-tab']);
