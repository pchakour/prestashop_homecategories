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
        ];
    }
}
