(function ($, paymentsSettings) {
  $(document).one('crmLoad', function () {
    var nonEditableFields = JSON.parse(paymentsSettings.nonEditableFields);

    (function init () {
      makeFieldsNonEditable();
      insertDeleteButtonAfterCancel();
    })();

    /**
     * This function makes form fields that are supposed to be
     * non editable based on some criteria to be non editable.
     */
    function makeFieldsNonEditable () {
      nonEditableFields.forEach(function (field) {
        var selector = '#' + field.name;
        if (field.type === 'select2') {
          $(selector).select2('readonly', true);
        } else if (field.type === 'crmdatetime') {
          $(selector).crmDatepicker('destroy').attr('readonly', true);
        } else {
          $(selector).attr('readonly', true);
        }
      });
    }
  });

  /**
   * Inserts the delete button to be right after cancel button.
   */
  function insertDeleteButtonAfterCancel () {
    $('#award_payment_delete').insertAfter('.crm-button_qf_AwardPayment_cancel');
  }

  $('#award_payment_delete').click(function (e) {
    e.preventDefault();
    var activityId = $('input[name="activity_id"]').val();
    CRM.confirm({ message: 'Please confirm you wish to delete this record?' }).on('crmConfirm:yes', function () {
      CRM.api3('Activity', 'delete', { id: activityId }).done(function (result) {
        if (result.is_error) {
          CRM.alert(result.error_message, 'Error deleting Record', 'error');
        }
        CRM.alert('Record deleted successfully', 'Record Deleted', 'success');
        window.location.href = '/civicrm/awardpayments';
      });
    });
  });
})(CRM.$, CRM['civiawards-payments-tab']);
