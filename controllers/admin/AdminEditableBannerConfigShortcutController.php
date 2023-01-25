<?php

/**
 * NOTICE OF LICENSE
 *
 * This file is licensed under the Software License Agreement.
 *
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author Arkonsoft
 * @copyright 2017-2023 Arkonsoft
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminEditableBannerConfigShortcutController extends ModuleAdminController {
    public function __construct(){        
        $token = Tools::getAdminTokenLite('AdminModules');
		parent::__construct();
		if ($this->module->active)
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules&token='.$token.'&configure=editablebanner&tab_module=administration&module_name=editablebanner',false));
    }
}