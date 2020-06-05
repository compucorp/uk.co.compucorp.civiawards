<div id="bootstrap-theme">
  <div class="civiawards__review-activity crm-form-block">
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="form-group row">
          {if $errorMessage}
            <p class="alert alert-warning">
              {$errorMessage}
            </p>
          {/if}
          {if $isViewAction}
            <div class="col-sm-5">
              <label for="source_contact_id">
                {ts}Added By{/ts}
              </label>
            </div>
            <div class="col-sm-7">
                {if !$isReviewFromSsp }
              <a
                class="view-contact no-popup"
                href=
                "{crmURL p="civicrm/contact/view" q="reset=1&cid=`$sourceContactId`"}"
                title="{ts}View Contact{/ts}"
              >
                {$sourceContactName}
              </a>
              {else}
                {$sourceContactName}
              {/if}
            </div>
          {/if}
          {if !$isViewAction}
            <div class="col-sm-5">
              {$form.source_contact_id.label}
            </div>
            <div class="col-sm-7">
              {$form.source_contact_id.html}
            </div>
          {/if}
          <div class="clear"></div>
        </div>
        {foreach from=$elementNames item=elementName}
          <div class="form-group row">
            <div class="col-sm-5">{$form.$elementName.label}</div>
            <div class="col-sm-7">{$form.$elementName.html}</div>
            <div class="clear"></div>
          </div>
        {/foreach}
      </div>
      <div class="crm-submit-buttons panel-footer clearfix">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
