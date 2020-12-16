<script type="text/javascript">
    {literal}
    CRM.$(function($) {
      CRM.$('#case_category_finance_management').insertAfter(CRM.$('#case_category_instance_type'));
      toggleFinanceManagementFieldVisibility();
      CRM.$('#case_category_instance_type').change(function () {
        toggleFinanceManagementFieldVisibility();
      });
    });

    function toggleFinanceManagementFieldVisibility() {
      var applicantManagementValue = {/literal} {$applicantManagement} {literal};
      var instanceType = CRM.$('#case_category_instance_type option:selected').val();

      if (instanceType == applicantManagementValue) {
        CRM.$('#case_category_finance_management').show();
      }
      else {
        CRM.$('#case_category_finance_management').hide();
      }
    }
    {/literal}
</script>

<table>
  <tr id="case_category_finance_management">
    <td class="label"> {$form.case_category_finance_management.label} </td>
    <td> {$form.case_category_finance_management.html} </td>
  </tr>
</table>
