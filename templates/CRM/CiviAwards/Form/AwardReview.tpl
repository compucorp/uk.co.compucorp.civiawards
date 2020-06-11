{if !$errorMessage}
  <div class="ssp-well-prefix">
    <a href="/ssp/awards/review-applications"><i class="fa fa-arrow-left"></i> Back to All Applications</a>
  </div>
{/if}
<div class="civiawards__review-activity well">
  {if $errorMessage}
    <p class="alert alert-warning">
      {$errorMessage}
    </p>
    <div class="clearfix">
      <a class="btn btn-primary pull-right" href="/ssp/awards/review-applications"> Back to all Applications </a>
    </div>
  {/if}
  <h1 class="page-header">
    {if $isViewAction}
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
    {/if}
    {if !$isViewAction}
      {$form.source_contact_id.html}
    {/if}
  </h1>
  {if !$errorMessage}
  <div class="ssp-details-page-section__sub-heading">
    <span class="ssp-applicant-card__award ssp-text-large">Dummy Award</span>
    <span>
      <span class="label label-primary" style="background-color: #f3e11b">Lorem ipsum</span>
      <span class="label label-primary" style="background-color: #d73737"> Dolor sit </span> </span>
    </div>
  {/if}
  {foreach from=$elementNames item=elementName}
    <div class="form-group">
      <label class="control-label" for="edit-name">{$form.$elementName.label}</label>
      <div class="ssp-form-control-description text-muted"> Lorem ipsum dolor sit. Sit dolor ipsum lorem porem. </div>
      {$form.$elementName.html}
    </div>
  {/foreach}
  {if !$errorMessage && !$isViewAction}
    <div class="clearfix">
      <button type="submit" class="btn btn-primary default validate pull-right"> Submit Application </button>
      <a class="btn btn-default pull-left" href="/ssp/awards/review-applications"> Cancel </a>
    </div>
  {/if}
</div>
