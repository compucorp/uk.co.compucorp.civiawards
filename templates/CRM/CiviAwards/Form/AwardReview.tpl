{if $isReviewFromSsp}
  {assign var='container_attributes' value=''}
  {assign var='wrapper_class' value='well'}
  {assign var='form_group_class' value=''}
  {assign var='form_group_label_class' value='control-label'}
  {assign var='form_group_field_class' value=''}
{else}
  {assign var='container_attributes' value='id="bootstrap-theme"'}
  {assign var='wrapper_class' value='crm-form-block'}
  {assign var='form_group_class' value='row'}
  {assign var='form_group_label_class' value='col-sm-5'}
  {assign var='form_group_field_class' value='col-sm-7'}
{/if}

{if !$errorMessage && $isReviewFromSsp}
  <div class="ssp-well-prefix">
    <a href="/ssp/awards/review-applications"><i class="fa fa-arrow-left"></i> Back to All Applications</a>
  </div>
{/if}

<div {$container_attributes}>
  <div class="civiawards__review-activity clearfix {$wrapper_class}">
    {if !$isReviewFromSsp}
      <div class="panel panel-default">
        <div class="panel-body">
    {/if}
      {* Show error mode *}
      {if $errorMessage}
        <p class="alert alert-warning">
          {$errorMessage}
        </p>
        {if $isReviewFromSsp}
          <div class="clearfix">
            <a class="btn btn-primary pull-right" href="/ssp/awards/review-applications"> Back to all Applications </a>
          </div>
        {/if}
      {* Show data mode *}
      {else}
        <div class="form-group {$form_group_class}">
          {if $isReviewFromSsp}
            <h1 class="header-pager">
              {if $isViewAction}
                {$sourceContactName}
              {else}
                {$form.source_contact_id.html}
              {/if}
            </h1>
            <div class="ssp-details-page-section__sub-heading">
              <span class="ssp-applicant-card__award ssp-text-large">{$caseTypeName}</span>
              <span>
                {foreach from=$caseTags item=caseTag}
                  <span class="label label-primary" style="background-color: {$caseTag.background_color}">{$caseTag.name}</span>
                {/foreach}
            </div>
          {else}
            {* View Mode *}
            {if $isViewAction}
              <div class="{$form_group_label_class}">
                <label for="source_contact_id">
                  {ts}Added By{/ts}
                </label>
              </div>
              <div class="{$form_group_field_class}">
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
            {/if} {*  End view mode *}
            {* Edit Mode *}
            {if !$isViewAction}
              <div class="{$form_group_label_class}">
                {$form.source_contact_id.label}
              </div>
              <div class="{$form_group_field_class}">
                {$form.source_contact_id.html}
              </div>
            {/if} {* End Edit mode *}
          {/if} {* End reviewBySSPUser *}
        </div>
      {/if}
      {* End if error message *}

      {* Form fields section Starts *}
      {foreach from=$elementData item=element}
        <div class="form-group {$form_group_class}">
          <label class="{$form_group_label_class}">{$form[$element.name].label}</label>
          {if $isReviewFromSsp}
            <div class="ssp-form-control-description text-muted"> {$element.help_post} </div>
          {/if}
          <div class="{$form_group_field_class}">{$form[$element.name].html}</div>
          <div class="clear"></div>
        </div>
      {/foreach}
      {* Form fields section Ends *}

      {* Form action section Starts *}
      {if !$errorMessage}
        {if $isReviewFromSsp}
          {if !$isViewAction}
            <div class="clearfix">
              <button type="submit" class="btn btn-primary default validate pull-right"> Submit Application </button>
              <a class="btn btn-default pull-left" href="/ssp/awards/review-applications"> Cancel </a>
            </div>
          {else}
            <button disabled="true" class="btn btn-primary default validate pull-right">
              <i class="fas fa-check"></i>
              Review Submitted
            </button >
          {/if}
        {else}
          <div class="crm-submit-buttons panel-footer clearfix">
              {if $isViewAction}
                <a href="{crmURL p='civicrm/awardreview' q=$editUrlParams}" class="edit button" title="{ts}Edit{/ts}"><span><i class="crm-i fa-pencil"></i> {ts}Edit{/ts}</span></a>
              {/if}
            {include file="CRM/common/formButtons.tpl" location="bottom"}
          </div>
        {/if}
      {/if}
      {* Form action section Ends *}

    {if !$isReviewFromSsp }
        </div>
      </div>
    {/if}
  </div>
</div>

