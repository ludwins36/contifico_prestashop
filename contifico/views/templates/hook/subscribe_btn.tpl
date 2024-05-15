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

<div class="wksubscribe-btn">
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="wkSubsBtnMenu" data-toggle="dropdown" aria-// haspopup="true" aria-expanded="false">
            <i class="material-icons">today</i>
            {$btnLabel}
        </button>
        <div class="dropdown-menu" aria-labelledby="wkSubsBtnMenu">
            {foreach from=$availableCycles item=cycles}
                <a class="dropdown-item add-to-subscription" data-frequency="{$cycles.frequency}" data-cycle="{$cycles.cycle}" data-id_product="{$cycles.id_product}" href="javascript:void(0);">{$cycles.frequencyText}</a>
            {/foreach}
        </div>
    </div>
</div>