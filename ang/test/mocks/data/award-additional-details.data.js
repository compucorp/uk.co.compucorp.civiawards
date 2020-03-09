(function () {
  var module = angular.module('civiawards.data');

  module.constant('AwardAdditionalDetailsMockData', {
    id: '1',
    case_type_id: '10',
    award_type: '1',
    start_date: '2019-10-29',
    end_date: '2019-11-29',
    award_manager: ['2', '1'],
    review_fields: [{
      id: '19',
      weight: 1,
      required: '1'
    }, {
      id: '20',
      weight: 2,
      required: '0'
    }, {
      id: '45',
      weight: 3,
      required: '0'
    }]
  });
}());
