{if $invalidAwardSubtype}
  <p class="alert alert-warning">
    Invalid Sub Type
  </p>
{else}
  <div class="crm-block crm-form-block crm-case-custom-form-block">
    {if $hasCustomFieldsAssigned}
      {include file="CRM/Custom/Form/CustomData.tpl" skipTitle=0}
      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    {else}
      <div class="crm-case-custom-form-block-empty text-center">No Custom Fields sets are available.<div>
    {/if}
  </div>
{/if}
