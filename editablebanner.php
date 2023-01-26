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

require_once(_PS_MODULE_DIR_. 'editablebanner/classes/EditableBannerConfig.php');

class EditableBanner extends Module
{
    public $id_config = 1;

    public function __construct()
    {
        $this->name = 'editablebanner';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Arkonsoft';
        $this->author_uri = 'https://arkonsoft.pl/';
        $this->need_instance = 1;
        $this->bootstrap = 1;

        parent::__construct();

        $this->displayName = $this->l('Editable Banner');
        $this->description = $this->l('Module adds banner that can be eddited from the back office');
        $this->confirmUninstall = $this->l('Are you sure? All data will be lost!');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->dependencies = array();
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return (parent::install()
            && $this->installTab()
            && $this->installTable()
            && $this->initTable()
            && $this->registerHook('displayBanner')
        );
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && $this->uninstallTab()
            && $this->uninstallTable()
            && $this->unregisterHook('displayBanner')
        );
    }

    public function installTab()
    {
        $tab = new Tab();
        $id_parent = (int)Tab::getIdFromClassName('AdminEditableBannerSettings');
        if(!$id_parent){
            $tab->id_parent = (int) Tab::getIdFromClassName('DEFAULT');
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->displayName;
            }
            $tab->class_name = 'AdminEditableBannerConfigShortcut';
            $tab->module = $this->name;
            $tab->active = 1;
        }
        return $tab->add();
    }

    public function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminEditableBannerConfigShortcut');
        $tab = new Tab((int)$id_tab);
        return $tab->delete();
    }

    public function installTable(){
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'editablebannerconfig` (
            `id_banner` int(11) NOT NULL AUTO_INCREMENT,
            `is_visible` boolean NOT NULL,
            `banner_text` TEXT NOT NULL,
            PRIMARY KEY (`id_banner`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($query);
    }
    
    public function uninstallTable(){
        $query = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'editablebannerconfig`';

        return Db::getInstance()->execute($query);
    }

    public function initTable(){
        $config = new EditableBannerConfig($this->id_config);
        $config->is_visible = true;
        $config->banner_text = '<p>Sample Text</p>';

        return (bool)$config->add();
    }

    public function hookDisplayBanner(){
        $config = $this->getConfigValues();

        $this->context->smarty->assign(
            array(
                'banner_text' => $config['banner_text'],
                'is_visible' => $config['is_visible']
            )
        );
        return $this->context->smarty->fetch($this->local_path.'views/templates/front/banner.tpl');
    }

    public function getContent()
    {
        if(Tools::isSubmit('submitEditableBannerModule')){
            $this->postProcess();
        }

        return $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEditableBannerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'name' => 'is_visible',
                        'desc' => $this->l('Show banner on page'),
                        'values' => array(
                            array(
                                'id' => 'is_visible_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'is_visible_off',
                                'value' => false,
                                'label' => $this->l('No')
                            ),
                        ),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'textarea',
                        'prefix' => '<i class="icon icon-cog"></i>',
                        'desc' => $this->l('Enter text of a banner'),
                        'name' => 'banner_text',
                        'autoload_rte' => 'rte',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );
    }

    protected function postProcess()
    {
        $banner_text = Tools::getValue('banner_text');
        $is_visible = (bool)Tools::getValue('is_visible');

        $config = new EditableBannerConfig($this->id_config);
        $config->banner_text = $banner_text;
        $config->is_visible = $is_visible;

        $config->update();
    }

    private function getConfigValues(){
        $config = new EditableBannerConfig($this->id_config);

        return array(
            'banner_text' => $config->banner_text,
            'is_visible' => $config->is_visible,
        );
    }
}