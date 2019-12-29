(function (angular) {
  var module = angular.module('civiawards');

  module.service('ReviewsCaseTab', ReviewsCaseTab);

  /**
   * Reviews Case Tab service.
   */
  function ReviewsCaseTab () {
    this.getPlaceholderUrl = function () {
      return '~/civiawards/reviews-tab/services/reviews-case-tab-placeholder.html';
    };

    this.activeTabContentUrl = function () {
      return '~/civiawards/reviews-tab/services/reviews-case-tab-content.html';
    };
  }
})(angular);
