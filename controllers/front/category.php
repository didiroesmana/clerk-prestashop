<?php
require "ClerkAbstractFrontController.php";

class ClerkCategoryModuleFrontController extends ClerkAbstractFrontController
{
    /**
     * Get response
     *
     * @return array
     */
    public function getJsonResponse()
    {
        $response = array();

        $categories = Category::getCategories(Configuration::get('PS_LANG_DEFAULT'), true, false);

        $id_lang = Configuration::get('PS_LANG_DEFAULT');

        foreach ($categories as $category) {
            if ($category['id_category'] === Configuration::get('PS_ROOT_CATEGORY')) {
                continue;
            }

            $item = [
                'id' => $category['id_category'],
                'name' => $category['name'],
                'url' => $this->context->link->getCategoryLink($category['id_category'], null, Configuration::get('PS_LANG_DEFAULT')),
            ];

            //Append parent id
            if ($category['id_parent'] !== Configuration::get('PS_ROOT_CATEGORY')) {
                $item['parent'] = $category['id_parent'];
            }

            //Append subcategories
            $categoryObj = new Category($category['id_category']);
            $subCategories = $categoryObj->getSubCategories($id_lang);

            $item['subcategories'] = array();
            foreach ($subCategories as $subCategory) {
                $item['subcategories'][] = (int)$subCategory['id_category'];
            }

            $response[] = $item;
        }

        return $response;
    }
}