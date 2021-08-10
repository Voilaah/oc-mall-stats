<?php namespace Voilaah\Mallstats\Models;

use Model;
use RainLab\User\Models\User;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

/**
 * orders Model
 */
class SalesExport extends \Backend\Models\ExportModel
{
    use JsonPrice {
        useCurrency as fallbackCurrency;
    }

    public $table = 'offline_mall_order_products';

    public $belongsTo = [
        'order' => Order::class,
        'variant' => Variant::class,
        'product' => Product::class,
    ];

    protected $appends  = [
        'order_number',
        'total_post_taxes',
        'categories',
    ];
    public $fillable =
     [
        'created_at_from',
        'created_at_to',
    ];

    public function exportData($columns, $sessionKey = null)
    {
        $query = self::query();
        if ($this->created_at_from) {
            $query->whereDate('created_at', '>=', $this->created_at_from );
        }
        if ($this->created_at_to) {
            $query->whereDate('created_at', '<=', $this->created_at_to );
        }
        $query->with([
                'order',
                'product.categories',
            ])
            ->orderBy('created_at', 'DESC');

        $sales = $query->get();
        return $sales->toArray();
    }

    public function getOrderNumberAttribute() {
        if ($this->order)
            return $this->order->order_number;
        return "";
    }

    public function getTotalPostTaxesAttribute() {
        return $this->totalPostTaxes()->float;
    }

    public function getCategoriesAttribute()
    {
        if ($this->product && $this->product->categories) {
            return $this->encodeArrayValue($this->product->categories->lists('name'));
        }
        return '';
    }

    ///

    public function totalPostTaxes()
    {
        return $this->toPriceModel('total_post_taxes');
    }

    protected function toPriceModel(string $key): Price
    {
        return new Price([
            'currency_id' => $this->useCurrency()->id,
            'price'       => $this->getOriginal($key) / 100,
        ]);
    }
}


