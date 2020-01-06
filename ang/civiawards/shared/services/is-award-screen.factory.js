(function (angular) {
  var module = angular.module('civiawards');

  module.factory('isAwardScreen', isAwardScreenFactory);

  /**
   * Is Award Screen factory.
   *
   * @param {object} $location the location service.
   * @returns {Function} The is award screen function.
   */
  function isAwardScreenFactory ($location) {
    return isAwardScreen;

    /**
     * Determines if the current screen belongs to awards.
     *
     * @returns {boolean} true when case type category url param is awards
     */
    function isAwardScreen () {
      var urlParams = $location.search();

      return urlParams.case_type_category === 'awards';
    }
  }
})(angular);
