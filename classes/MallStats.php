<?php

namespace Voilaah\Mallstats\Classes;

use DB;
use Illuminate\Support\Carbon;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\OrderState;

class MallStats
{
    protected $productsTable;
    protected $ordersTable;
    protected $statesTable;

    public function __construct()
    {
        $this->productsTable      = (new Product())->table;
        $this->ordersTable      = (new Order())->table;
        $this->statesTable      = (new OrderState())->table;
    }

    public function bestSellers()
    {
        $bestSellers = DB::table($this->productsTable)
                    ->select('id', 'name', 'sales_count')
                    ->whereNull('deleted_at')
                    ->orderBy('sales_count', 'DESC')
                    ->limit(10)
                    ->get()
                    ;

        if ( ! $bestSellers) {
            return [];
        }
        return $bestSellers;

    }
}
