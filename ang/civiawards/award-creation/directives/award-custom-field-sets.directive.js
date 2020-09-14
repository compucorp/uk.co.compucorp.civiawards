(function (angular, $, _, getCrmUrl) {
  var module = angular.module('civiawards');

  module.directive('civiawardCustomFieldSets', function ($q) {
    return {
      link: civiawardCustomFieldSetsLink,
      templateUrl: '~/civiawards/award-creation/directives/award-custom-field-sets.directive.html',
      restrict: 'E'
    };

    /**
     * @param {object} scope scope
     * @param {object} element element
     * @param {object} attrs attributes
     */
    function civiawardCustomFieldSetsLink (scope, element, attrs) {
      var customFieldSetsTabObj;

      (function init () {
        customFieldSetsTabObj = _.find(scope.tabs, function (tabObj) {
          return tabObj.name === 'customFieldSets';
        });
        customFieldSetsTabObj.save = save;

        loadCustomFieldSetScreen();
      }());

      /**
       * Load Custom field set PHP screen
       *
       * @returns {object} loaded form element
       */
      function loadCustomFieldSetScreen () {
        var url = '/civicrm/award/customfield?entityId=' + scope.awardId;

        return CRM
          .loadForm(url, {
            target: $(element).find('.civiaward__custom-field-sets__container')
          });
      }

      /**
       * Save the custom field set PHP form by triggering the hidden save button
       * using jQuery
       *
       * @returns {Promise} promise
       */
      function save () {
        var defer = $q.defer();
        var submitButtonLink = '.civiaward__custom-field-sets__container .award-custom-field';

        $(submitButtonLink).trigger('click');

        element.on('crmFormSuccess', function () {
          $(element).find('.civiaward__custom-field-sets__container').unblock();
          defer.resolve();
        });

        return defer.promise;
      }
    }
  });
})(angular, CRM.$, CRM._, CRM.url);
