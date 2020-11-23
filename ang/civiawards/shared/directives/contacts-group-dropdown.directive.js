(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardContactsGroupDropdown', function (crmApi, isTruthy) {
    return {
      link: civiawardContactsGroupDropdownLink,
      restrict: 'A',
      require: 'ngModel'
    };

    /**
     * Contacts Group Dropdown Link function
     *
     * @param {object} scope scope object of the controller
     * @param {object} elem directives element
     * @param {object} attr attributes of the directive element
     * @param {object} ngModel ng model service
     */
    function civiawardContactsGroupDropdownLink (scope, elem, attr, ngModel) {
      scope.groups = {
        more: false,
        results: [{
          text: 'Include Group',
          children: []
        }, {
          text: 'Exclude Group',
          children: []
        }]
      };

      (function init () {
        ngModel.$render = refreshUI;

        fetchGroupsData()
          .then(function (data) {
            var groupsData = getGroupsDataForSelect2(data);

            scope.groups.results[0].children = groupsData.include;
            scope.groups.results[1].children = groupsData.exclude;
          })
          .then(initSelect2)
          .then(refreshUI);
      }());

      /**
       * Initialise Select 2
       */
      function initSelect2 () {
        elem.select2({
          containerCssClass: 'civiaward__contacts-group__container',
          multiple: true,
          data: scope.groups,
          dropdownAutoWidth: true,
          formatResult: formatItem,
          formatSelection: formatItem
        });

        elem.on('select2-selecting', selectEventHandler);

        $(elem).on('select2-removing', removeEventHandler);
      }

      /**
       * Prepare Groups data to be shown on Select2 UI
       *
       * @param {Array} groupsData groups data
       * @returns {object} groups data
       */
      function getGroupsDataForSelect2 (groupsData) {
        var returnData = {
          include: [], exclude: []
        };

        _.chain(groupsData)
          .filter(function (group) {
            return !isTruthy(group.is_hidden) && isTruthy(group.is_active);
          })
          .each(function (group) {
            returnData.include.push({
              id: 'include_' + group.id,
              value: group.id,
              mode: 'include',
              text: group.title
            });

            returnData.exclude.push({
              id: 'exclude_' + group.id,
              value: group.id,
              mode: 'exclude',
              text: group.title
            });
          }).value();

        return returnData;
      }

      /**
       * Fetch Groups data using API
       *
       * @returns {Promise} promise
       */
      function fetchGroupsData () {
        return crmApi('Group', 'get', {
          options: { limit: 0 }
        }).then(function (groupsData) {
          return groupsData.values;
        });
      }

      /**
       * Select event handler for dropdown
       *
       * @param {object} event event object
       */
      function selectEventHandler (event) {
        if (event.object.mode === 'exclude') {
          ngModel.$modelValue.exclude.push(event.object.value);
          arrayRemove(ngModel.$modelValue.include, event.object.value);
        } else {
          ngModel.$modelValue.include.push(event.object.value);
          arrayRemove(ngModel.$modelValue.exclude, event.object.value);
        }

        refreshUI();
        scope.$apply();
        $(elem).select2('close');
        event.preventDefault();
      }

      /**
       * Remove event handler for dropdown
       *
       * @param {object} event event object
       */
      function removeEventHandler (event) {
        arrayRemove(ngModel.$modelValue[event.choice.mode], event.choice.value);

        refreshUI();
        scope.$parent.$apply();

        event.preventDefault();
      }

      /**
       * Show selected values in dropddown
       */
      function refreshUI () {
        scope.recips = ngModel.$modelValue;
        if (ngModel.$modelValue) {
          $(elem).select2('val', convertGroupsToValues(ngModel.$modelValue));
        }
      }

      /**
       * Convert selected values to the format suitable for Select2
       *
       * @param {object} values selected values
       * @returns {Array} selected values in format suitable for Select2
       */
      function convertGroupsToValues (values) {
        return [].concat(
          _.map(values.include, function (contactId) { return 'include_' + contactId; }),
          _.map(values.exclude, function (contactId) { return 'exclude_' + contactId; })
        );
      }

      /**
       * Remove value from sent array
       *
       * @param {Array} array array from where value will be removed
       * @param {number} value value to be removed
       */
      function arrayRemove (array, value) {
        var index = array.indexOf(value);

        if (index >= 0) {
          array.splice(index, 1);
        }
      }

      /**
       * Format dropdown item
       *
       * @param {object} item item to be formatted
       * @returns {string} markup to be shown
       */
      function formatItem (item) {
        if (!item.id) {
          return item.text; // return `text` for optgroup
        }

        var spanClass = (item.mode === 'exclude')
          ? 'civiaward__contacts-group__item--exclude'
          : 'civiaward__contacts-group__item--include';

        return '<i class="crm-i fa-users"></i> <span class="' + spanClass + '">' + item.text + '</span>';
      }
    }
  });
})(angular, CRM.$, CRM._);
