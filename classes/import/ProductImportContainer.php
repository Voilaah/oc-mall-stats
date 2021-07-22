<?php

namespace Voilaah\MallStats\Classes\Import;

use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\ProductPrice;

class ProductImportContainer extends ImportDemoProduct
{
    protected $params;

    public function __construct($args)
    {
        $this->params = [];
        $this->params['id']                 = array_get($args, 'id', null);
        $this->params['brand']              = array_get($args, 'brand', null);
        $this->params['user_defined_id']    = array_get($args, 'user_defined_id', null);
        $this->params['name']               = array_get($args, 'name', 'Missing name');
        $this->params['auto_published']     = array_get($args, 'auto_published', false);
        $this->params['slug']               = array_get($args, 'slug', null);
        $this->params['description_short']  = array_get($args, 'description_short', null);
        $this->params['description']        = array_get($args, 'description', null);
        $this->params['weight']             = (int)array_get($args, 'weight', null);
        $this->params['height']             = (int)array_get($args, 'height', null);
        $this->params['width']              = (int)array_get($args, 'width', null);
        $this->params['length']             = (int)array_get($args, 'length', null);
        $this->params['price']              = (double)array_get($args, 'price', 0.0);
        $this->params['stock']              = (int)array_get($args, 'stock', 1);
        $this->params['allow_out_of_stock_purchases']          = (bool)array_get($args, 'allow_out_of_stock_purchases', false);
        $this->params['properties']         = array_get($args, 'properties', []);
        $this->params['categories']         = array_get($args, 'categories', []);
        $this->params['imagePath']          = array_get($args, 'imagePath', null);
        $this->params['addImagePath']       = array_get($args, 'addImagePath', null);
        $this->params['additional_descriptions']          = array_get($args,'additional_descriptions', []);

    }

    protected function attributes(): array
    {
        $brand = $this->params['brand'];
        $weight = $this->params['weight'];
        $height = $this->params['height'];
        $width = $this->params['width'];
        $length = $this->params['length'];
        $price = $this->params['price'];
        $stock = $this->params['stock'];
        $allow_out_of_stock_purchases = $this->params['allow_out_of_stock_purchases'];
        $name = $this->params['name'];
        $slug = $this->params['slug'];
        $id = $this->params['id'];
        $user_defined_id = $this->params['user_defined_id'];
        $description_short = $this->params['description_short'];
        $description = $this->params['description'];
        $additional_descriptions = $this->params['additional_descriptions'];
        $auto_published = $this->params['auto_published'];

        return [
            'brand_id'                     => $brand,
            'user_defined_id'              => $user_defined_id,
            'name'                         => $name,
            'slug'                         => $slug ?? str_slug($name),
            'description_short'            => $description_short,
            'description'                  => $description,
            'meta_title'                   => $name,
            'meta_description'             => $description_short,
            'meta_keywords'                => '',
            'weight'                       => $weight,
            'height'                       => $height,
            'width'                        => $width,
            'length'                       => $length,
            'inventory_management_method'  => 'single',
            'stock'                        => $stock,
            'quantity_default'             => 1,
            // 'quantity_min'                 => 1,
            // 'quantity_max'                 => 5,
            'allow_out_of_stock_purchases' => $allow_out_of_stock_purchases,
            'links'                        => null,
            'stackable'                    => false,
            'shippable'                    => true,
            'price_includes_tax'           => true,
            'mpn'                          => null,
            'published'                    => $auto_published ?? false,
            'additional_descriptions'      => [
                                                $additional_descriptions
                                            ],
        ];
    }


    protected function taxes(): array
    {
        return [];
    }

    protected function properties(): array
    {
        return array_get($this->params, 'properties', null);
    }

    protected function categories(): array
    {
        // array of category id
        return $this->params['categories'];
        // return [
        //     $this->category($this->params['categories'])->id,
        // ];
    }

    protected function prices(): array
    {
        $currency_id = Currency::defaultCurrency()->id;
        return [
            new ProductPrice([
                'currency_id' => $currency_id,
                'price' => $this->params['price']
            ])
        ];
    }


    protected function additionalPrices(): array
    {
        return [];
    }

    protected function variants(): array
    {
        return [];
    }

   protected function customFields(): array
    {
        return [];
    }

    protected function images(): array
    {
        if (!empty($this->params['imagePath'])) {
            return [
                [
                    'name'        => 'Main images',
                    'is_main_set' => true,
                    'images'      => [
                        $this->params['imagePath'],
                    ],
                ],
            ];
        }

        return [];

    }

    protected function reviews(): array
    {
        return [];
    }
}
