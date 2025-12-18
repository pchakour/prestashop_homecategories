<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Pc_HomeCategories extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'pc_homecategories';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Pharès CHAKOUR';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Home Categories Display');
        $this->description = $this->l('Displays top-level categories with images on the homepage.');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitAddconfiguration')) {
            Configuration::updateValue(
                'HOME_CATEGORIES_CUSTOM_CSS',
                Tools::getValue('HOME_CATEGORIES_CUSTOM_CSS'),
                true // allow HTML / CSS
            );

            return $this->displayConfirmation($this->l('Settings updated'));
        }

        return $this->renderForm();
    }

    protected function renderForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Custom CSS'),
                        'name'  => 'HOME_CATEGORIES_CUSTOM_CSS',
                        'desc'  => $this->l('Custom CSS injected on the home page'),
                        'rows'  => 10,
                        'cols'  => 60,
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        $helper->fields_value = [
            'HOME_CATEGORIES_CUSTOM_CSS' =>
            Configuration::get('HOME_CATEGORIES_CUSTOM_CSS'),
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerStylesheet(
            'pc_homecategories_css',
            'modules/' . $this->name . '/views/css/homecategories.css'
        );
    }

    public function renderWidget($hookName, array $configuration)
    {
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        return $this->fetch('module:pc_homecategories/views/templates/hook/widget.tpl');
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $idLang = (int)$this->context->language->id;
        $idShop = (int)$this->context->shop->id;

        // Get root category
        $rootCategory = Category::getRootCategory($idLang);

        // Get first-level categories
        $categories = Category::getChildren($rootCategory->id, $idLang, true, $idShop);

        // Add cover image URLs
        foreach ($categories as &$cat) {
            $imagePath = _PS_CAT_IMG_DIR_ . $cat['id_category'] . '.jpg';
            if (file_exists($imagePath)) {
                $cat['image_url'] = $this->context->link->getCatImageLink(
                    $cat['link_rewrite'],
                    $cat['id_category'],
                    'category_default'
                );
            } else {
                $cat['image_url'] = _THEME_CAT_DIR_ . 'default.jpg';
            }

            $cat['link'] = $this->context->link->getCategoryLink(
                $cat['id_category'],
                $cat['link_rewrite']
            );
        }

        return [
            'home_categories' => $categories,
            'custom_css' => Configuration::get('HOME_CATEGORIES_CUSTOM_CSS'),
        ];
    }
}
