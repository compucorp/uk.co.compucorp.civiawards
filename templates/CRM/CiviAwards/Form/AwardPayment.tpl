<div id="bootstrap-theme">
  <div class="panel panel-default">
    <div class="panel-body">
        {foreach from=$elementNames item=elementName}
            {if $elementName == 'separator'}
                <hr>
            {else}
              <div class="form-group row">
                <label class="col-sm-2">{$form.$elementName.label}</label>
                <div class="col-sm-10">{$form.$elementName.html}</div>
              </div>
            {/if}
        {/foreach}
    </div>
    <div class="crm-submit-buttons panel-footer clearfix">
        {if $isViewAction}
          <a href="{crmURL p='civicrm/awardpayment' q=$editUrlParams}" class="edit button" title="{ts}Edit{/ts}"><span><i class="crm-i fa-pencil"></i> {ts}Edit{/ts}</span></a>
        {/if}
        {if $isViewAction || $isUpdateAction}
          <a href="#" class="delete button" title="{ts}Delete{/ts}"><span><i class="crm-i fa-trash"></i> {ts}Delete{/ts}</span></a>
        {/if}
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
</div>
