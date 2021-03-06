<?php
if (!defined('_PS_VERSION_')) {
	exit;
}

class Clerk extends Module
{
	/**
	 * Clerk constructor.
	 */
	public function __construct()
	{
		$this->name = 'clerk';
		$this->tab = 'advertising_marketing';
		$this->version = '1.0.0';
		$this->author = 'Clerk';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Clerk');
		$this->description = $this->l('Clerk.io Turns More Browsers Into Buyers');
	}

	/**
	 * Register hooks & create configuration
	 *
	 * @return bool
	 */
	public function install()
	{
		if (Shop::isFeatureActive()) {
			Shop::setContext(Shop::CONTEXT_ALL);
		}

		//initialize empty settings
		Configuration::updateValue('CLERK_PUBLIC_KEY', '');
		Configuration::updateValue('CLERK_PRIVATE_KEY', '');

		Configuration::updateValue('CLERK_SEARCH_ENABLED', 0);
		Configuration::updateValue('CLERK_SEARCH_TEMPLATE', '');

		Configuration::updateValue('CLERK_LIVESEARCH_ENABLED', 0);
		Configuration::updateValue('CLERK_LIVESEARCH_INCLUDE_CATEGORIES', 0);
		Configuration::updateValue('CLERK_LIVESEARCH_TEMPLATE', '');

		Configuration::updateValue('CLERK_POWERSTEP_ENABLED', 0);
		Configuration::updateValue('CLERK_POWERSTEP_TEMPLATES', '');

		return parent::install() &&
            $this->registerHook('top') &&
			$this->registerHook('footer') &&
			$this->registerHook('displayOrderConfirmation');
	}

	/**
	 * Delete configuration
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		Configuration::deleteByName('CLERK_PUBLIC_KEY');
		Configuration::deleteByName('CLERK_PRIVATE_KEY');
		Configuration::deleteByName('CLERK_SEARCH_ENABLED');
		Configuration::deleteByName('CLERK_SEARCH_TEMPLATE');
		Configuration::deleteByName('CLERK_LIVESEARCH_ENABLED');
		Configuration::deleteByName('CLERK_LIVESEARCH_INCLUDE_CATEGORIES');
		Configuration::deleteByName('CLERK_LIVESEARCH_TEMPLATE');
		Configuration::deleteByName('CLERK_POWERSTEP_ENABLED');
		Configuration::deleteByName('CLERK_POWERSTEP_TEMPLATES');

		// Delete configuration
		return parent::uninstall();
	}

	/**
	 * Save configuration and show form
	 */
	public function getContent()
	{
		$output = '';

		if (Tools::isSubmit('submitClerk')) {
			Configuration::updateValue('CLERK_PUBLIC_KEY', Tools::getValue('clerk_public_key', ''));
			Configuration::updateValue('CLERK_PRIVATE_KEY', Tools::getValue('clerk_private_key', ''));
			Configuration::updateValue('CLERK_SEARCH_ENABLED', Tools::getValue('clerk_search_enabled', 0));
			Configuration::updateValue('CLERK_SEARCH_TEMPLATE', Tools::getValue('clerk_search_template', ''));
			Configuration::updateValue('CLERK_LIVESEARCH_ENABLED', Tools::getValue('clerk_livesearch_enabled', 0));
			Configuration::updateValue('CLERK_LIVESEARCH_INCLUDE_CATEGORIES', Tools::getValue('clerk_livesearch_include_categories', ''));
			Configuration::updateValue('CLERK_LIVESEARCH_TEMPLATE', Tools::getValue('clerk_livesearch_template', ''));
			Configuration::updateValue('CLERK_POWERSTEP_ENABLED', Tools::getValue('clerk_powerstep_enabled', 0));
			Configuration::updateValue('CLERK_POWERSTEP_TEMPLATES', Tools::getValue('clerk_powerstep_templates', ''));

			$output .= $this->displayConfirmation($this->l('Settings updated.'));
		}

		return $output.$this->renderForm();
	}

	/**
	 * Render configuration form
	 *
	 * @return mixed
	 */
	public function renderForm()
	{
		//General settings
		$this->fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('General'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Public Key'),
						'name' => 'clerk_public_key',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Private Key'),
						'name' => 'clerk_private_key',
					),
					array(
						'type' => 'text',
						'label' => $this->l('Import Url'),
						'name' => 'clerk_import_url',
						'readonly' => true,
					),
				),
			),
		);

		//Search settings
		$this->fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Search Settings'),
					'icon' => 'icon-search'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'clerk_search_enabled',
						'values' => array(
							array(
								'id' => 'clerk_search_enabled_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'clerk_search_enabled_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Template'),
						'name' => 'clerk_search_template',
					),
				),
			),
		);

		//Livesearch settings
		$this->fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Live search Settings'),
					'icon' => 'icon-search'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'clerk_livesearch_enabled',
						'values' => array(
							array(
								'id' => 'clerk_livesearch_enabled_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'clerk_livesearch_enabled_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Include Categories'),
						'name' => 'clerk_livesearch_include_categories',
						'values' => array(
							array(
								'id' => 'clerk_include_categories_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'clerk_include_categories_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Template'),
						'name' => 'clerk_livesearch_template',
					),
				),
			),
		);

		//Powerstep settings
		$this->fields_form[] = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Powerstep Settings'),
					'icon' => 'icon-shopping-cart'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'clerk_powerstep_enabled',
						'values' => array(
							array(
								'id' => 'clerk_powerstep_enabled_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'clerk_powerstep_enabled_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Templates'),
						'name' => 'clerk_powerstep_templates',
						'desc' => $this->l('A comma separated list of clerk templates to render')
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = $lang->id;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitClerk';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm($this->fields_form);
	}

	/**
	 * Get configuration field values
	 * @return array
	 */
	public function getConfigFieldsValues()
	{
		return array(
			'clerk_public_key' => Tools::getValue('clerk_public_key', Configuration::get('CLERK_PUBLIC_KEY')),
			'clerk_private_key' => Tools::getValue('clerk_private_key', Configuration::get('CLERK_PRIVATE_KEY')),
			'clerk_import_url' => _PS_BASE_URL_,
			'clerk_search_enabled' => Tools::getValue('clerk_search_enabled', Configuration::get('CLERK_SEARCH_ENABLED')),
			'clerk_search_template' => Tools::getValue('clerk_search_template', Configuration::get('CLERK_SEARCH_TEMPLATE')),
			'clerk_livesearch_enabled' => Tools::getValue('clerk_livesearch_enabled', Configuration::get('CLERK_LIVESEARCH_ENABLED')),
			'clerk_livesearch_include_categories' => Tools::getValue('clerk_livesearch_include_categories', Configuration::get('CLERK_LIVESEARCH_INCLUDE_CATEGORIES')),
			'clerk_livesearch_template' => Tools::getValue('clerk_livesearch_template', Configuration::get('CLERK_LIVESEARCH_TEMPLATE')),
			'clerk_powerstep_enabled' => Tools::getValue('clerk_powerstep_enabled', Configuration::get('CLERK_POWERSTEP_ENABLED')),
			'clerk_powerstep_templates' => Tools::getValue('clerk_powerstep_templates', Configuration::get('CLERK_POWERSTEP_TEMPLATES')),
		);
	}

    public function hookTop($params)
    {
        if (Configuration::get('CLERK_SEARCH_ENABLED')) {
            $key = $this->getCacheId('clerksearch-top' . (( ! isset($params['hook_mobile']) || ! $params['hook_mobile']) ? '' : '-hook_mobile'));
            if (Tools::getValue('search_query') || ! $this->isCached('search-top.tpl', $key)) {
                //            $this->calculHookCommon($params);
                $this->smarty->assign(array(
                        'clerksearch_type' => 'top',
                        'search_query'     => (string)Tools::getValue('search_query'),
                        'livesearch_enabled' => (bool)Configuration::get('CLERK_LIVESEARCH_ENABLED'),
                        'livesearch_include_categories' => (int)Configuration::get('CLERK_LIVESEARCH_INCLUDE_CATEGORIES'),
                        'livesearch_template' => Tools::strtolower(str_replace(' ', '-', Configuration::get('CLERK_LIVESEARCH_TEMPLATE'))),
                    )
                );
            }

            return $this->display(__FILE__, 'search-top.tpl', Tools::getValue('search_query') ? null : $key);
        }
    }

	/**
	 * Add visitor tracking to footer
	 *
	 * @return mixed
	 */
	public function hookFooter()
	{
		//Assign template variables
		$this->context->smarty->assign(
			array(
				'clerk_public_key' => Configuration::get('CLERK_PUBLIC_KEY', ''),
			)
		);

		return $this->display(__FILE__, 'visitor_tracking.tpl', $this->getCacheId(BlockCMSModel::FOOTER));
	}

	/**
	 * Add sales tracking to order confirmation
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function hookDisplayOrderConfirmation($params)
	{
		$order = $params['objOrder'];
		$products = $order->getProducts();

		$productArray = array();

		foreach ($products as $product) {
			$productArray[] = array(
				'id' => $product['id_product'],
				'quantity' => $product['product_quantity'],
				'price' => $product['product_price_wt'],
			);
		}

		$this->context->smarty->assign(
			array(
				'clerk_order_id' => $order->id,
				'clerk_customer_email' => $this->context->customer->email,
				'clerk_products' => json_encode($productArray),
			)
		);

		return $this->display(__FILE__, 'sales_tracking.tpl');
	}
}