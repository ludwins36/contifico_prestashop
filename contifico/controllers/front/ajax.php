
<?php
/**
 * 2007-2019 PrestaShop
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
 *  @copyright 2007-2019 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ContificoAjaxModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->validator = new ValidarIdentificacion();

    }

    public function postProcess()
    {
        $id = Context::getContext()->cart->id_customer;
        if(Tools::getValue('action') == 'setTypeDoc'){
            $this->context->cookie->__set('type_doc_' . $id,  Tools::getValue('type'));
            $this->context->cookie->write();
            echo json_encode("success");
            exit();
        }else if(Tools::getValue('action') == 'setTypePer'){
            $this->context->cookie->__set('type_per_' . $id,  Tools::getValue('type'));
            $this->context->cookie->write();
            echo json_encode("success");
            exit();
        }else if(Tools::getValue('action') == 'validateDni'){
            $doc = Tools::getValue('dni');
            if(Tools::getValue("type") == "J"){
                $valid = ($this->validator->validarRucSociedadPrivada($doc) || $this->validator->validarRucSociedadPublica($doc));
            }else{
                $valid = ($this->validator->validarRucPersonaNatural($doc) || $this->validator->validarCedula($doc));
            }
            echo json_encode(["valid" => $valid, "type" => Tools::getValue("type")  ]);
        }
        // var_dump($this->validator->validarCedula('0931018733'));
    }
}
