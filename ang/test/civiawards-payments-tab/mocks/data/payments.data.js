((_, angular) => {
  const module = angular.module('civiawards-payments-tab.mocks');

  module.constant('mockPayments', [
    {
      id: _.uniqueId(),
      custom_11: '1',
      custom_12: 'GBP',
      custom_13: '1099',
      custom_14: 'N9TT-9G0A-B7FQ-RANC',
      activity_date_time: '2000-01-01 12:00:00',
      target_contact_name: { 10: 'Jon Snow' },
      'status_id.label': 'paid_complete',
      'status_id.name': 'paid_complete',
      type: 'Awards Payment'
    },
    {
      id: _.uniqueId(),
      custom_11: '2',
      custom_12: 'GBP',
      custom_13: '1050',
      custom_14: 'QK6A-JI6S-7ETR-0A6C',
      activity_date_time: '2000-05-01 12:00:00',
      target_contact_name: { 11: 'Jon Snow' },
      'status_id.label': 'paid_complete',
      'status_id.name': 'paid_complete',
      type: 'Awards Payment'
    },
    {
      id: _.uniqueId(),
      custom_11: '1',
      custom_12: 'GBP',
      custom_13: '1250',
      custom_14: 'SXFP-CHYK-ONI6-S89U',
      activity_date_time: '2000-07-01 12:00:00',
      target_contact_name: { 12: 'Jon Snow' },
      'status_id.label': 'failed_incomplete',
      'status_id.name': 'failed_incomplete',
      type: 'Awards Payment'
    }
  ]);
})(CRM._, angular);
