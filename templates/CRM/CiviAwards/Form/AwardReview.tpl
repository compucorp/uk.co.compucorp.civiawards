<div id="bootstrap-theme">
  <div class="panel panel-default crm-form-block crm-absence_type-form-block crm-leave-and-absences-form-block">
    <div class="panel-heading">
      <h1 class="panel-title">
          {$caseContactDisplayName} - {$caseTypeName} - {$caseTags}
      </h1>
    </div>
    <div class="panel-body">
        {foreach from=$elementNames item=elementName}
          <div class="form-group row">
            <div class="col-sm-3">{$form.$elementName.label}</div>
            <div class="col-sm-9">{$form.$elementName.html}</div>
            <div class="clear"></div>
          </div>
        {/foreach}
    </div>
  </div>
</div>

<div class="crm-submit-buttons">
  <div class="panel-footer clearfix">
    <div class="pull-right">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
</div>





