{if $invalidAwardSubtype}
  <p class="alert alert-warning">
    {ts}Invalid Sub Type{/ts}
  </p>
{else}
  <div class="crm-block crm-form-block crm-case-custom-form-block">
    {if $hasCustomFieldsAssigned}
      {include file="CRM/Custom/Form/CustomData.tpl" skipTitle=0}
      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    {else}
      <div class="crm-case-custom-form-block-empty text-center">{ts}No Custom Fields sets are available.{/ts}<div>
    {/if}
  </div>
{/if}
