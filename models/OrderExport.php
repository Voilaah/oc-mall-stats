<?php namespace Voilaah\Mallstats\Models;

use Model;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Models\OrderProduct;
use OFFLINE\Mall\Classes\Traits\JsonPrice;

/**
 * orders Model
 */
class OrderExport extends \Backend\Models\ExportModel
{
    use JsonPrice {
        useCurrency as fallbackCurrency;
    }

    public $table = 'offline_mall_orders';

    public $jsonable = [
        'discounts',
    ];

    public $hasMany = [
        'products'         => [OrderProduct::class, 'key' => 'order_id'],
        'virtual_products' => [OrderProduct::class, 'scope' => 'virtual'],
    ];

    public $belongsTo = [
        'order_state'             => [OrderState::class, 'deleted' => true],
        'customer'                => [Customer::class, 'deleted' => true],
    ];

    protected $appends  = [
        'customer_name',
        'customer_email',
        'order_state_name',
        'payment_state_name',
        'shipped_status',
        'discounts_savings',
        'total_discounts',
        'items',
    ];


    public function exportData($columns, $sessionKey = null)
    {
        $orders = self::make()->with([
                        'customer',
                        'products',
                        'products.product',
                    ])
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
        return $orders;
    }

    public function getItemsAttribute()
    {
        $results = [];
        if ($this->products) {
            foreach ($this->products as $key => $product) {
                $results[$product->id] = $product->id . '-' . $product->name . '-' . $product->quantity;
            }
            return $this->encodeArrayValue($results);
        }
        return "";
    }

    public function getCustomerNameAttribute() {
        if ($this->customer)
            return $this->customer->firstname . ' ' . $this->customer->lastname;
        return "";
    }

    public function getCustomerEmailAttribute() {
        if ($this->customer && $this->customer->user)
            return $this->customer->user->email;
        return "";
    }

    public function getOrderStateNameAttribute() {
        if ($this->order_state)
            return $this->order_state->name;
        return "";
    }

    public function getPaymentStateNameAttribute() {
        if ($this->payment_state) {

            return $this->payment_state::label();
        }
        return "";
    }

    public function getShippedStatusAttribute() {
        return $this->is_shipped ? 'shipped' : 'not shipped';
    }

    public function getTotalPostTaxesAttribute() {
        return $this->totalPostTaxes()->float;
    }

    public function getTotalShippingPostTaxesAttribute() {
        return $this->totalShippingPostTaxes()->float;
    }

    public function getDiscountsSavingsAttribute() {
        $results = [];
        foreach($this->discounts as $key => $discount) {
            $results[$discount['discount']['name']] =
                $discount['savings_formatted']
            ;
        }
        return $this->encodeArrayValue($results);
    }

    public function getTotalDiscountsAttribute() {
        $total_discount = 0;
        foreach($this->discounts as $key => $discount) {
            $total_discount += $discount['savings'];
        }

        return (new Price([
            'currency_id' => $this->useCurrency()->id,
            'price'       => $total_discount / 100,
        ]))->float;
        // return $results / 100;
    }

    ///

    public function totalShippingPostTaxes()
    {
        return $this->toPriceModel('total_shipping_post_taxes');
    }

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
