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
  <div class="civiawards__crm-popup__container clearfix {$wrapper_class}">
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
              {$caseContactDisplayName}
            </h1>
            <div class="ssp-details-page-section__sub-heading">
              <span class="ssp-applicant-card__award ssp-text-large">{$caseTypeName}</span>
              <span>
                {foreach from=$caseTags item=caseTag}
                  <span class="badge label label-primary" style="background-color: {$caseTag.background_color}">{$caseTag.name}</span>
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
          <div class="{$form_group_field_class}">
            {if $form[$element.name].type === 'file' && $existingFileName && $existingFileUrl}
              <a href="{$existingFileUrl}" target="_blank">{$existingFileName}</a>
            {else}
              {$form[$element.name].html}
            {/if}
          </div>
          <div class="clear"></div>
        </div>
      {/foreach}
      {* Form fields section Ends *}

      {* Review Status Section Starts *}
      {* View Mode *}
      {if !$isReviewFromSsp}
          {if $isViewAction}
            <div class="form-group {$form_group_class}">
              <label class="{$form_group_label_class}">{ts}Status{/ts}</label>
              <div class="{$form_group_field_class}">{$activityStatus}</div>
              <div class="clear"></div>
            </div>
          {else} {*  End view mode *}
            <div class="form-group {$form_group_class}">
              <label class="{$form_group_label_class}">{$form.status_id.label}</label>
              <div class="{$form_group_field_class}">{$form.status_id.html}</div>
              <div class="clear"></div>
            </div>
          {/if}
      {/if}
      {* Review Status Section Endss *}

      {* Form action section Starts *}
      {if !$errorMessage}
        {if $isReviewFromSsp}
          {if !$isViewAction}
            <div class="clearfix">
              <a class="btn btn-default pull-left" href="/ssp/awards/review-applications">{ts} Cancel {/ts}</a>
              {include file="CRM/common/formButtons.tpl"}
            </div>
          {else}
            <button disabled="true" class="btn btn-primary default validate pull-right">
              <i class="fas fa-check"></i>
              <span>Review Submitted</span>
            </button>
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

{literal}
  <script type="text/javascript">
    CRM.$(function ($) {
      $(document).ready(function () {
        $('form').preventDoubleSubmission();
      });

      $.fn.preventDoubleSubmission = function () {
        CRM.$(this).on('submit', function (e) {
          if ( $(this)[0].checkValidity() ) {
            CRM.$.blockUI();
          }
        });

        return this;
      };
    });
  </script>
{/literal}
