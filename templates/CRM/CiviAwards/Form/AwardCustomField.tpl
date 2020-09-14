{if $invalidAwardType}
  <p class="alert alert-warning">
    Invalid Award Type
  </p>
{else}
  <div class="crm-block crm-form-block crm-case-custom-form-block">
    {include file="CRM/Custom/Form/CustomData.tpl" skipTitle=0}
    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
{/if}
