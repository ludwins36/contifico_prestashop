/**
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
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function ($) {
    var dataContifico = {
        valueSelect: '',
        valueSelectPersona: '',
        start: function () {
            if (typeof prestashop !== 'null' && typeof select_temp !== 'null') {
                if (prestashop.page.page_name == 'checkout') {
                    if (select_temp) {
                        if ($("input[name='dni']").is(':visible')) {
                            $('input[name="dni"]').closest(".form-group").before(select_temp)

                        }
                    }
                }
            }
            this.eventUpdate();
        },
        eventUpdate: function () {
            let timeDni;
            let tplDanger = "<p id='leyend-dni'>Documento invalido por favor verifique.</p>"

            $('input[name="dni"]').on("keydown", (e) => {
                window.clearTimeout(timeDni);
                timeDni = window.setTimeout(() => {
                    // code
                    var url = "index.php?fc=module&module=contifico&controller=ajax";
                    $.ajax({
                        url: url,
                        dataType: 'json',
                        type: "POST",
                        data: {
                            action: "validateDni",
                            dni: $(e.target).val(),
                            type:  $("select[name='type_persona']").val(),
                        },
                        success: function (response) {
                            console.log(response)
                            if(response.valid){
                                $(e.target).css({"border-color" : "#0de90d", "border-width" : "2px"});
                                $('button[name="confirm-addresses"]').attr( "disabled", false );
                                $("#leyend-dni").remove()
                                
                            }else{
                                $(e.target).css({"border-color" : "red", "border-width" : "2px"});
                                $('button[name="confirm-addresses"]').attr( "disabled", "disabled" );
                                $("#leyend-dni").remove()
                                $(e.target).after(tplDanger)
                                $(e.target).trigger("focus");

                            }
                        },
                    });
                }, 1000);
            });

            $("select[name='type_dni']").on("change", (e) => {
                this.valueSelect = $(e.target).val();
                var url = "index.php?fc=module&module=contifico&controller=ajax";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        action: "setTypeDoc",
                        type: this.valueSelect
                    },
                    success: function (response) {
                        console.log(response)
                    },
                });

            })

            $("select[name='type_persona']").on("change", (e) => {
                this.valueSelectPersona = $(e.target).val();
                var url = "index.php?fc=module&module=contifico&controller=ajax";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        action: "setTypePer",
                        type: this.valueSelectPersona
                    },
                    success: function (response) {
                        console.log(response)
                    },
                });

                if ($(e.target).val() == 'J') {
                    $("#content-name").show();
                } else {
                    $("#content-name").hide();

                }
            })

            prestashop.on("updatedAddressForm", (e) => {
                if (typeof prestashop !== 'null') {
                    if (prestashop.page.page_name == 'checkout') {
                        if ($("input[name='dni']").is(':visible')) {
                            if (!$("select[name='type_dni']").is(':visible')) {
                                let template_selected = '<div class="form-group row align-items-center ">' +
                                    '<label class="col-md-2 col-form-label required">Tipo de Documento</label>' +
                                    '<div class="col-md-8">' +
                                    '<div class="custom-select2">' +
                                    '<select class="form-control form-control-select js-country" name="type_dni" required="">' +
                                    '<option value="" disabled="" selected="">-- por favor, seleccione --</option>';

                                if (this.valueSelect == 'ruc') {
                                    template_selected += '<option value="ruc" selected>Ruc</option>' +
                                        '<option value="dni">Cedula</option>';
                                } else {
                                    template_selected += '<option value="ruc" >Ruc</option>' +
                                        '<option value="dni" selected>Cedula</option>';
                                }

                                template_selected += '</select></div></div>' +
                                    '<div class="col-md-2 form-control-comment"></div></div>';
                                $('input[name="dni"]').closest(".form-group").before(template_selected);

                            }

                        }

                    }
                }
            })
        },
    };
    dataContifico.start();
});