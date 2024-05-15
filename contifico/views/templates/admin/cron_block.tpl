{*
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*}

<form class="form-horizontal" action="{$form_action}" method="post">
    <div class="panel col-lg-12">
        <div class="panel-heading">
            <i class="icon-cog"></i>
            {if $title}
                {l s=$title mod='contifico'}
            {else}
                {l s='Cron Link' mod='wkproductsubscription'}
            {/if}
        </div>
        <div class="form-wrapper">
            <div class="alert alert-info">
                {l s='Please add this link to your server cron job and schedule it to run daily.'  mod='wkproductsubscription'}
            </div>
           
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Enter secure string to protect cron url' mod='wkproductsubscription'}">{l s='Cron Token' mod='wkproductsubscription'}</span>
                </label>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="CONTIFICO_CRON_TOKEN" value="{$cron_token}" required>
                </div>
                <div class="col-lg-3"></div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Please do not access this link directly.' mod='wkproductsubscription'}">{l s='Cron Link' mod='wkproductsubscription'}</span>
                </label>
                <div class="col-lg-9">
                    <input class="form-control" name="cron_link" value="{$cron_link}" readonly>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Please do not access this link directly.' mod='wkproductsubscription'}">{l s='Cron Link Update' mod='wkproductsubscription'}</span>
                </label>
                <div class="col-lg-9">
                    <input class="form-control" name="cron_link_update" value="{$cron_link_up}" readonly>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="cron_form_submit_btn" name="submitCronSettings" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='wkproductsubscription'}
            </button>
        </div>
    </div>
</form>