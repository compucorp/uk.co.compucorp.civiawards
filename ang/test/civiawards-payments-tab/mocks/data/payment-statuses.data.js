((_) => {
  CRM['civicase-base'].activityStatuses = _.extend(
    {},
    CRM['civicase-base'].activityStatuses,
    {
      11: {
        value: '11',
        label: 'Applied for (incomplete)',
        name: 'applied_for_incomplete',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '11',
        filter: '0'
      },
      12: {
        value: '12',
        label: 'Approved (complete)',
        name: 'approved_complete',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '12',
        filter: '0'
      },
      13: {
        value: '13',
        label: 'Exported (complete)',
        name: 'exported_complete',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '13',
        filter: '0'
      },
      14: {
        value: '14',
        label: 'Paid (complete)',
        name: 'paid_complete',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '14',
        filter: '0'
      },
      15: {
        value: '15',
        label: 'Cancelled (cancelled)',
        name: 'cancelled_cancelled',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '15',
        filter: '0'
      },
      16: {
        value: '16',
        label: 'Failed (incomplete)',
        name: 'failed_incomplete',
        grouping: 'Awards Payments',
        is_active: '1',
        weight: '16',
        filter: '0'
      }
    }
  );
})(CRM._);
