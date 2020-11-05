(function () {
  var module = angular.module('civiawards.data');

  CRM.civiawards.awardSubtypes = {
    1: {
      value: '1',
      label: 'Medal',
      name: 'Medal',
      weight: '1'
    },
    2: {
      value: '2',
      label: 'Prize',
      name: 'Prize',
      weight: '2'
    },
    3: {
      value: '3',
      label: 'Programme',
      name: 'Programme',
      weight: '3'
    },
    4: {
      value: '4',
      label: 'Scholarship',
      name: 'Scholarship',
      weight: '4'
    },
    5: {
      value: '5',
      label: 'Award',
      name: 'Award',
      weight: '5'
    },
    6: {
      value: '6',
      label: 'Grant',
      name: 'Grant',
      weight: '6'
    }
  };

  module.constant('AwardSubtypeMockData', CRM.civiawards.awardSubtypes);
}());
