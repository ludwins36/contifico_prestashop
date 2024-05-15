{*
* 2007-2020 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="form-group row align-items-center ">
    <label class="col-md-2 col-form-label required">
        {l s="Tipo de Persona"}
    </label>
    <div class="col-md-8">
        <div class="custom-select2">
            <select class="form-control form-control-select" name="type_persona" required="">
                <option value="N" {if $persone == 'N' || $persone == ''}selected{/if}>Natural</option>
                <option value="J" {if $persone == 'J'}selected{/if}>Juridica</option>
            </select>
        </div>
    </div>
    <div class="col-md-2 form-control-comment">
    </div>
</div>


<div class="form-group row align-items-center" id="content-name"  {if $persone == 'N' || $persone == ''} style="display:none;" {/if}>
    <label class="col-md-2 col-form-label required">
        {l s="Nombre de la empresa"}
    </label>
    <div class="col-md-8">
        <input class="form-control" name="name_juridit" type="text" value="{$empresa}" maxlength="32">
    </div>
    <div class="col-md-2 form-control-comment">
    </div>
</div>


<div class="form-group row align-items-center ">
    <label class="col-md-2 col-form-label required">
        {l s="Tipo de Documento"}
    </label>
    <div class="col-md-8">
        <div class="custom-select2">
            <select class="form-control form-control-select" name="type_dni" required="">
                <option value="" disabled="" {if $def == ''}selected{/if}>-- por favor, seleccione --</option>
                <option value="ruc" {if $def == 'ruc'}selected{/if}>Ruc</option>
                <option value="dni" {if $def == 'dni'}selected{/if}>Cedula</option>
            </select>
        </div>
    </div>
    <div class="col-md-2 form-control-comment">
    </div>
</div>