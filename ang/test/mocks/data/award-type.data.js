(function () {
  var module = angular.module('civiawards.data');

  CRM.civiawards.awardTypes = {
    1: {
      value: '1',
      label: 'Medal',
      name: 'Array',
      weight: '1'
    },
    2: {
      value: '2',
      label: 'Prize',
      name: 'Array',
      weight: '2'
    },
    3: {
      value: '3',
      label: 'Programme',
      name: 'Array',
      weight: '3'
    },
    4: {
      value: '4',
      label: 'Scholarship',
      name: 'Array',
      weight: '4'
    },
    5: {
      value: '5',
      label: 'Award',
      name: 'Array',
      weight: '5'
    },
    6: {
      value: '6',
      label: 'Grant',
      name: 'Array',
      weight: '6'
    }
  };

  module.constant('AwardTypeMockData', CRM.civiawards.awardTypes);
}());
