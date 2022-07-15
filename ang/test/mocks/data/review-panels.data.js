(function () {
  var module = angular.module('civiawards.data');

  module.constant('ReviewPanelsMockData', [
    {
      id: '46',
      title: 'New Panel',
      case_type_id: '62',
      contact_settings: {
        exclude_groups: ['1', '1001'],
        include_groups: ['2', '1002'],
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
        ],
        case_roles: ['Has application reviewed by']
      },
      visibility_settings: {
        application_status: ['1', '1001'],
        anonymize_application: '1',
        application_tags: ['1', '12', '15', '1001'],
        is_application_status_restricted: '1',
        restricted_application_status: ['1', '2']
      },
      is_active: '1'
    }, {
      id: '47',
      title: 'New Panel 2',
      case_type_id: '62',
      contact_settings: {
        exclude_groups: [],
        include_groups: [],
        relationship: [],
        case_roles: []
      },
      visibility_settings: {
        application_status: [],
        anonymize_application: '0',
        application_tags: []
      },
      is_active: '0'
    }
  ]);
}());
