(function () {
  var module = angular.module('civiawards.data');

  module.constant('ReviewPanelsMockData', [
    {
      id: '46',
      title: 'New Panel',
      case_type_id: '62',
      contact_settings: {
        exclude_groups: ['1'],
        include_groups: ['2'],
        relationship: [
          {
            is_a_to_b: '1',
            relationship_type_id: '14',
            contact_id: ['4', '2']
          },
          {
            is_a_to_b: '0',
            relationship_type_id: '14',
            contact_id: ['3', '1']
          }
        ]
      },
      is_active: '1'
    }
  ]);
}());
