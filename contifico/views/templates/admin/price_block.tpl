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
            {l s='Politicas de Precios' mod='Contifico'}
        </div>
        <div class="form-wrapper">
            <div class="alert alert-info">
                {l s='Por favor defina las reglas de precio para la sincronización con Contifico.'  mod='Contifico'}
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="" data-original-title="{l s='Por porcentaje' mod='Contifico'}">{l s='Regla de precios' mod='Contifico'}</span>
                </label>
                <div class="col-lg-3 p-2">
                    <input type='radio' id="contifico_porcentaje" name='CONTIFICO_AMOUNT'  {if $price == 0}checked {/if} value='0' /> {l s='Por porcentaje' mod='vexglovo'}<br>
				    <input type='text' id="porcentaje" name='CONTIFICO_AMOUNT_PRICE_POR'  placeholder='00' value='{$porcentaje}' style="display: none;"/>
				    <input type='radio'  id="contifico_monto" name='CONTIFICO_AMOUNT' {if $price == 1}checked {/if}  value='1' /> {l s='Monto Fijo' mod='vexglovo'}<br>
				    <input type='text' id="monto" name='CONTIFICO_AMOUNT_PRICE_FIJO' placeholder='00.00' value='{$fijo}' style="display: none;/>
                </div>
                <div class="col-lg-3"></div>
            </div>
            
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="cron_form_submit_btn" name="submitPriceSettings" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Guardar' mod='Contifico'}
            </button>
        </div>
    </div>
</form>