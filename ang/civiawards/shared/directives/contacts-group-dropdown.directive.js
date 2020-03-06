(function (angular, $, _) {
  var module = angular.module('civiawards');

  module.directive('civiawardContactsGroupDropdown', function (crmApi) {
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
          .then(prepareGroupsDataForSelect2)
          .then(initSelect2)
          .then(refreshUI);

        scope.$watchCollection('recips.include', refreshUI);
        scope.$watchCollection('recips.exclude', refreshUI);
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
       * @param {Array} groupsData groups data
       */
      function prepareGroupsDataForSelect2 (groupsData) {
        _.chain(groupsData)
          .filter(function (group) {
            return group.extra.is_hidden === '0' && group.extra.is_active === '1';
          })
          .each(function (group) {
            scope.groups.results[0].children.push({
              id: 'i' + group.id,
              value: group.id,
              mode: 'include',
              text: group.label
            });
            scope.groups.results[1].children.push({
              id: 'e' + group.id,
              value: group.id,
              mode: 'exclude',
              text: group.label
            });
          }).value();
      }

      /**
       * Fetch Groups data using API
       *
       * @returns {Promise} promise
       */
      function fetchGroupsData () {
        return crmApi('Group', 'getlist', {
          options: { limit: 0 },
          extra: ['is_hidden', 'is_active']
        }).then(function (groupsData) {
          return groupsData.values;
        });
      }

      /**
       * Select event handler for dropdown
       *
       * @param {object} e event object
       */
      function selectEventHandler (e) {
        if (e.object.mode === 'exclude') {
          ngModel.$modelValue.exclude.push(e.object.value);
          arrayRemove(ngModel.$modelValue.include, e.object.value);
        } else {
          ngModel.$modelValue.include.push(e.object.value);
          arrayRemove(ngModel.$modelValue.exclude, e.object.value);
        }

        scope.$apply();
        $(elem).select2('close');
        e.preventDefault();
      }

      /**
       * Remove event handler for dropdown
       *
       * @param {object} e event object
       */
      function removeEventHandler (e) {
        arrayRemove(ngModel.$modelValue[e.choice.mode], e.choice.value);

        scope.$parent.$apply();

        e.preventDefault();
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
        var r = [];

        _.each(values.include, function (v) {
          r.push('i' + v);
        });

        _.each(values.exclude, function (v) {
          r.push('e' + v);
        });

        return r;
      }

      /**
       * Remove value from sent array
       *
       * @param {Array} array array from where value will be removed
       * @param {number} value value to be removed
       */
      function arrayRemove (array, value) {
        var idx = array.indexOf(value);
        if (idx >= 0) {
          array.splice(idx, 1);
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
