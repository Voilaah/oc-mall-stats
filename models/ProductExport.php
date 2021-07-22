<?php namespace Voilaah\MallStats\Models;

use ApplicationException;
use Backend\Models\ExportModel;
use OFFLINE\Mall\Models\ProductPrice;

/**
 * Post Export Model
 */
class ProductExport extends ExportModel
{
    use \October\Rain\Database\Traits\SoftDelete;
    use \OFFLINE\Mall\Classes\Traits\PriceAccessors;
    use \OFFLINE\Mall\Classes\Traits\ProductPriceAccessors;

    public $table = 'offline_mall_products';

    public $belongsToMany = [
        'product_categories' => [
            'OFFLINE\Mall\Models\Category',
            'table'    => 'offline_mall_category_product',
            'key'      => 'product_id',
            'otherKey' => 'category_id',
        ]
    ];
    public $hasMany = [
        'prices'                 => [
            ProductPrice::class,
            'conditions' => 'variant_id is null',
            'key' => 'product_id',
            ],
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'categories',
        'product_price',
    ];

    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()
            ->with([
                'product_categories'
            ])
            ->get()
            ->toArray()
        ;

        return $result;
    }

    public function getProductPriceAttribute()
    {
        return $this->price()->string;
    }

    public function getCategoriesAttribute()
    {
        if (!$this->product_categories) return '';
        return $this->encodeArrayValue($this->product_categories->lists('name'));
    }
}
