<div id="bootstrap-theme">
  <div class="civiawards__crm-popup__container clearfix crm-form-block">
    <div class="civiawards__payments-form">
      <div class="panel panel-default">
        <div class="panel-body">
            {foreach from=$elementNames item=elementName}
                {if $elementName == 'separator'}
                    <hr>
                {else}
                    {if in_array($elementName, $fieldsAfterCurrencyTypes)}
                      <div class="civiawards__payments-form__input civiawards__payments-form__input--double">{$form.$elementName.html}</div></div>
                    {else}
                      <div class="form-group row">
                        <label class="civiawards__payments-form__label">{$form.$elementName.label}</label>
                        {if !in_array($elementName, $currencyTypeFields)}
                        <div class="civiawards__payments-form__input civiawards__payments-form__input--single">{$form.$elementName.html}
                        </div>
                        {else}
                        <div class="civiawards__payments-form__input civiawards__payments-form__input--double">{$form.$elementName.html}
                        {/if}
                      </div>
                    {/if}
                {/if}
            {/foreach}
        </div>
      </div>
    </div>
    <div class="civiawards__payments-form__attachments">
      {include file="CRM/Form/attachment.tpl"}
    </div>
    <div class="crm-submit-buttons panel-footer clearfix">
        {if $isViewAction && !$isActivityStatusExported}
          <a href="{crmURL p='civicrm/awardpayment' q=$editUrlParams}" class="edit button" title="{ts}Edit{/ts}"><span><i class="crm-i fa-pencil"></i> {ts}Edit{/ts}</span></a>
        {/if}
        {if $isViewAction || $isUpdateAction}
            {if (call_user_func(array('CRM_Core_Permission','check'), 'delete activities') && !$activityStatusIsLocked) || call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM')}
              <a id='award_payment_delete' class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash"></i> {ts}Delete{/ts}</span></a>
            {/if}
        {/if}
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
</div>
