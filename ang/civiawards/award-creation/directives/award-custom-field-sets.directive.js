(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardCustomFieldSets', function ($q, civicaseCrmLoadForm) {
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

        if (scope.awardId) {
          loadCustomFieldSetScreen();
        }
      }());

      /**
       * Load Custom field set PHP screen
       *
       * @returns {object} loaded form element
       */
      function loadCustomFieldSetScreen () {
        var url = '/civicrm/award/customfield?entityId=' + scope.awardId;

        return civicaseCrmLoadForm(url, {
          target: $(element).find('.civiaward__custom-field-sets__container')
        });
      }

      /**
       * Save the custom field set PHP form by triggering the hidden save button
       * using jQuery.
       *
       * If there is no custom fields form, it will continue as normal.
       *
       * @returns {Promise} promise
       */
      function save () {
        var $submitButtonLink = $('.civiaward__custom-field-sets__container .award-custom-field');
        var hasCustomFields = $submitButtonLink.length > 0;
        var defer = $q.defer();

        if (!scope.awardId || !hasCustomFields) {
          defer.resolve();
        }

        $submitButtonLink.trigger('click');

        element.on('crmFormSuccess', function () {
          $(element).find('.civiaward__custom-field-sets__container').unblock();
          defer.resolve();
        });

        return defer.promise;
      }
    }
  });
})(angular, CRM.$, CRM._);
