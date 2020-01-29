<?php
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
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Wim_statspixel extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'wim_statspixel';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Webimpacto Consulting S.L.';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Stats Pixel');
        $this->description = $this->l('Analytics and Stats .');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('WIM_STATSPIXEL_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        Configuration::deleteByName('WIM_STATSPIXEL_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWim_statspixelModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        
        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWim_statspixelModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
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
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter the project key'),
                        'name' => 'WIM_STATSPIXEL_ACCOUNT_KEY',
                        'label' => $this->l('Project Key'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter the API Host'),
                        'name' => 'WIM_STATSPIXEL_ACCOUNT_HOST',
                        'label' => $this->l('Api Host'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'WIM_STATSPIXEL_ACCOUNT_KEY' => Configuration::get('WIM_STATSPIXEL_ACCOUNT_KEY'),
            'WIM_STATSPIXEL_ACCOUNT_HOST' => (Configuration::get('WIM_STATSPIXEL_ACCOUNT_HOST')) ? Configuration::get('WIM_STATSPIXEL_ACCOUNT_HOST') : 'https://api.stats.t1.webimpacto.net',
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayOrderConfirmation($params)
    {
        $projectKey = Configuration::get('WIM_STATSPIXEL_ACCOUNT_KEY');
        $hostApi = Configuration::get('WIM_STATSPIXEL_ACCOUNT_HOST');



        if(!$hostApi){
            $hostApi = 'https://api.stats.t1.webimpacto.net';  
        }

        if($projectKey){


        
        $idOrder = (isset($params['order'])) ? $params['order']->id : ((isset($params['objOrder'])) ? $params['objOrder']->id : 0);
        $totalOrder = (isset($params['order'])) ? $params['order']->total_paid : ((isset($params['objOrder'])) ? $params['objOrder']->total_paid : 0);

        $return = '<iframe src="'.$hostApi.'/order?idOrder='.$idOrder.'&project=WEB&total='.$totalOrder.'" height="0" width="0" style="display:none;visibility:hidden"></iframe>';


        return $return;   

        } 
    }
}
