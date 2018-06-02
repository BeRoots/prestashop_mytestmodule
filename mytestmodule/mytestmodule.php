<?php
/**
* 2007-2018 PrestaShop
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
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mytestmodule extends Module
{
    // Define two properties coresponding to inputs in admin form
    private $with_footer_content = false;
    private $with_footer_custom_content = false;

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'mytestmodule';
        $this->tab = 'seo';
        $this->version = '1.0.0';
        $this->author = 'beroots';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('mytestmodule display name');
        $this->description = $this->l('A description for mytestmodule');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall mytestmodule ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('MYTESTMODULE_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('displayCustomFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MYTESTMODULE_LIVE_MODE');

        //define special hooks for this module
        include(dirname(__FILE__).'/sql/uninstall.php');

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
        if (((bool)Tools::isSubmit('submitMytestmoduleModule')) == true) {
            $this->postProcess();
        }
        
        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
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
        $helper->submit_action = 'submitMytestmoduleModule';
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
                    /*array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'MYTESTMODULE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),*/
                    array(
                        'type' => 'switch',
                        'label' => $this->l('With Common Structured Data'),
                        'name' => 'MYTESTMODULE_WITH_FOOTER_CONTENT',
                        'is_bool' => true,
                        'desc' => $this->l('Add "Hello footer hook!" in your prestashop footer source code.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                        'hint' => $this->l('If enabled, the module add "Hello footer hook!" in your prestashop footer source code.')
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('With Common Structured Data'),
                    'name' => 'MYTESTMODULE_WITH_CUSTOM_FOOTER_CONTENT',
                    'is_bool' => true,
                    'desc' => $this->l('Add "Hello custom hook!" in your prestashop footer source code.'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'hint' => $this->l('If enabled, the module add "Hello custom hook!" in your prestashop footer source code.')
                ),
            ),
            'buttons' => array(
                    'cancel' => array(
                        'title' => $this->l('Cancel'),
                        'href' => '#',
                        'js' => 'window.history.back();',
                        'icon' => 'process-icon-cancel'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitMytestmoduleModule',
                    'icon' => 'process-icon-save'
                )
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            //'MYTESTMODULE_LIVE_MODE' => Configuration::get('MYTESTMODULE_LIVE_MODE', true),
            'MYTESTMODULE_WITH_FOOTER_CONTENT' => Configuration::get('MYTESTMODULE_WITH_FOOTER_CONTENT', false),
            'MYTESTMODULE_WITH_CUSTOM_FOOTER_CONTENT' => Configuration::get('MYTESTMODULE_WITH_CUSTOM_FOOTER_CONTENT', false),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        if (Tools::isSubmit('submitMytestmoduleModule')) {
            foreach (array_keys($form_values) as $key) {
                // process _POST inputs
                if (in_array($key, array(
                    'MYTESTMODULE_WITH_FOOTER_CONTENT',
                    'MYTESTMODULE_WITH_CUSTOM_FOOTER_CONTENT'))
                ) {
                    Configuration::updateValue($key, Tools::getValue($key));
                }

                if ($key == 'MYTESTMODULE_WITH_FOOTER_CONTENT') {
                    $this->with_labels = Tools::getValue($key);
                } elseif ($key == 'MYTESTMODULE_WITH_CUSTOM_FOOTER_CONTENT') {
                    $this->with_header_background = Tools::getValue($key);
                }
            }
        }
    }

    public function hookDisplayFooter()
    {
        if ($this->with_footer_content === true) {
            return $this->display(__FILE__, 'hello_footer.tpl', $this->getCacheId());
        } else {
            return false;
        }
    }

    public function hookDisplayCustomFooter()
    {
        if ($this->with_footer_custom_content === true) {
            return $this->display(__FILE__, 'hello_custom_footer.tpl', $this->getCacheId());
        } else {
            return false;
        }
    }
}
