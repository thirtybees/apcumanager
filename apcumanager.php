<?php
/**
 * Copyright (C) 2017-2025 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2017-2025 thirty bees
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    http_response_code(403);
    exit('Access denied');
}

/**
 * Class ApcuManager
 */
class ApcuManager extends Module
{
    public function __construct()
    {
        $this->name = 'apcumanager';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'thirty bees';
        $this->bootstrap = true;
        $this->need_instance = false;

        parent::__construct();

        $this->displayName = $this->l('thirty bees APCu Cache Manager');
        $this->description = $this->l('View and manage your APCu cache directly from your back office');
        
        if (!extension_loaded('apcu')) {
            $this->warning = $this->l('APCu extension is not installed or enabled.');
        }
    }

    /**
     * @return string
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitClearCache')) {
            $output .= $this->clearCache();
        }

        $this->context->smarty->assign([
            'module_dir' => $this->_path,
        ]);

        $output .= $this->renderForm();

        return $output . $this->display(__FILE__, 'views/templates/admin/apc.tpl');
    }

    /**
     * Clears the APCu cache.
     *
     * @return string
     */
    public function clearCache()
    {
        if (!function_exists('apcu_clear_cache') || !apcu_clear_cache()) {
            return $this->displayError($this->l('Failed to clear APCu cache.'));
        }
        return $this->displayConfirmation($this->l('APCu cache cleared successfully.'));
    }

    /**
     * Renders the configuration form
     *
     * @return string
     *
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function renderForm()
    {
        $apcuStatus = function_exists('apcu_enabled') && apcu_enabled()
            ? '<span class="badge badge-success">' . $this->l('Enabled.') . '</span>'
            : '<span class="badge badge-danger">' . $this->l('Not enabled.') . '</span>';

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('APCu Cache Control'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'  => 'free',
                        'label' => $this->l('APCu Status'),
                        'name'  => 'apcu_status',
                        'desc'  => $apcuStatus,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Clear APCu Cache'),
                    'name' => 'submitClearCache',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-eraser',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitClearCache';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper->generateForm([$fieldsForm]);
    }
}
