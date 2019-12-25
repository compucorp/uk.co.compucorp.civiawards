<div id="bootstrap-theme">
  <div class="civiawards__review-activity crm-form-block">
    <div class="panel panel-default">
      <div class="panel-body">
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
