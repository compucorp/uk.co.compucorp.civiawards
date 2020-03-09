(function () {
  var module = angular.module('civiawards.data');

  module.constant('AwardMockData', [{
    id: '10',
    name: 'new_award',
    title: 'New Award',
    description: 'Description',
    is_active: '1',
    weight: '1',
    definition: {
      statuses: [
        'Open',
        'Closed',
        'Urgent'
      ]
    },
    case_type_category: '3',
    is_forkable: '1',
    is_forked: ''
  }, {
    id: '10',
    name: 'new_award_2',
    title: 'New Award 2',
    description: 'Description 2',
    is_active: '1',
    weight: '2',
    definition: {
      statuses: [
        'Open',
        'Closed'
      ]
    },
    case_type_category: '3',
    is_forkable: '1',
    is_forked: ''
  }]
  );
}());
