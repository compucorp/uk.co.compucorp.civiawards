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
      ],
      restrictActivityAsgmtToCmsUser: '0',
      activityTypes: [
        {
          name: 'Open Case',
          max_instances: '1'
        },
        {
          name: 'Medical evaluation'
        },
        {
          name: 'Applicant Review',
          max_instances: '2'
        }
      ],
      activitySets: [
        {
          name: 'tl1',
          label: 'Timeline 1',
          timeline: '1',
          activityTypes: [
            {
              name: 'Medical evaluation',
              reference_activity: 'Open Case',
              reference_offset: '1',
              reference_select: 'newest'
            }
          ]
        }
      ],
      timelineActivityTypes: [
        {
          name: 'Mental health evaluation',
          reference_activity: 'Open Case',
          reference_offset: '1',
          reference_select: 'newest'
        }
      ],
      caseRoles: [
        {
          name: 'Homeless Services Coordinator',
          creator: '1',
          manager: '1'
        },
        {
          name: 'Application Manager',
          creator: '0',
          manager: '1'
        }
      ]
    },
    case_type_category: '3',
    is_forkable: '1',
    is_forked: ''
  }, {
    id: '11',
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
