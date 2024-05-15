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

<div id="contifico-block">
    <div class="row">
        <div class="col-lg-12">
            {* {if $displayShopWarning}
                <div class="alert alert-warning">
                    <p class="alert-text">
                        {l s='Select a shop to add this subscription product neither it will be added to default shop' mod='contifico'}
                    </p>
                </div>
            {/if} *}
            <div class="alert alert-info">
                <p class="alert-text">
                    {l s='Seleccione la categoria de contifico.' mod='contifico'}
                </p>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="form-wrapper">
        <div class="row mt-25">
            <div class="col-lg-12">
                <h3>{l s='Contifico Opciones' mod='contifico'}</h3>
            </div>
            <div class="form-group col-lg-3">
                <label class="form-control-label">
                    {l s='Crear o actualizar Producto en contifico.' mod='contifico'}</span>
                    <span class="help-box" data-toggle="popover"
                        data-content="{l s='Allow this product as subscription.' mod='contifico'}"
                        data-original-title="" title=""></span>
                </label>
                <div for="product_contifico_allow">
                    <input data-toggle="switch" class="" id="product_contifico_allow" data-inverse="true"
                        type="checkbox" value="1" name="allow_contifico" {if $dataContifico} checked="checked" {/if}>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row mt-25" id="data_contifico" style="display:{if $dataContifico == false}none{/if};">
            <div class="col-lg-12">
                <h3>{l s='Configuracion Contifico' mod='contifico'}</h3>
            </div>

            <div class="form-group col-lg-3">
                <label class="form-control-label">
                    {l s='Categorias' mod='contifico'}</span>
                    <span class="help-box" data-toggle="popover"
                        data-content="{l s='Selecciona una de las categorias de contifico.' mod='contifico'}"
                        data-original-title="" title=""></span>
                </label>
                <div for="">
                    <select class="form-control frequency" name="contifico_categoria">
                        {foreach from=$categorys item=item}
                            {if $item.padre_id == null}
                                <option {if $item.id == $dataContifico.categoryId}selected{/if}
                                    value="{$item.id}:{$item.nombre}">{$item.nombre}</option>
                            {/if}

                        {/foreach}

                    </select>

                </div>
            </div>

            <div class="form-group col-lg-3">
                <label class="form-control-label">
                    {l s='Bodegas' mod='contifico'}</span>
                    <span class="help-box" data-toggle="popover"
                        data-content="{l s='Selecciona una de las Bodegas activas de contifico.' mod='contifico'}"
                        data-original-title="" title=""></span>
                </label>
                <div for="">
                    <select class="form-control frequency" name="contifico_bodega">
                        {foreach from=$bodegas item=item}
                            {if $item.venta}
                                <option {if $item.id == $dataContifico.bodega}selected{/if} value="{$item.id}">{$item.nombre}
                                </option>
                            {/if}

                        {/foreach}

                    </select>

                </div>
            </div>

        </div>
        <input type="hidden" name="contifico_add_product" value="1">

    </div>
</div>
<script>

</script>