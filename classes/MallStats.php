<?php

namespace Voilaah\Mallstats\Classes;

use DB;
use Config;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Carbon;
use OFFLINE\Mall\Models\Order;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\OrderState;
use OFFLINE\Mall\Classes\Stats\OrdersStats;
use OFFLINE\Mall\Classes\PaymentState\PaidState;

class MallStats extends OrdersStats
{
    protected $productsTable;
    protected $ordersTable;
    protected $statesTable;
    protected $cancelledStateId;

    public function __construct()
    {
        parent::__construct();
        $this->productsTable      = (new Product())->table;
        $this->ordersTable      = (new Order())->table;
        $this->statesTable      = (new OrderState())->table;
        $this->customersTable      = (new Customer())->table;
        $this->cancelledStateId = optional(OrderState::where('flag', OrderState::FLAG_CANCELLED)->first())->id;
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

    public function currentMonthDataSet(): string
    {
        $timeZone = Config::get('backend.timezone'); // October v.2.x
        if (!$timeZone) {
            $timeZone = Config::get('app.timezone'); // Fallback to October v.1.x
        }
        $timeZoneObj = new DateTimeZone($timeZone);

        $dataset = DB::table($this->ordersTable)
                 ->whereNull('deleted_at')
                 ->select(
                    //  DB::raw('UNIX_TIMESTAMP(created_at) as `time`'),
                     DB::raw('year(created_at) as `year`'),
                     DB::raw('month(created_at) as `month`'),
                     DB::raw('monthname(created_at) as `monthname`'),
                     DB::raw('count(id) as `data`')
                 )
                 ->groupBy("year", "month", "monthname" )
                 ->orderBy("year", "DESC")
                 ->orderBy("month", "DESC")
                 ->get()
                 ->toArray();
        $result = [];
        foreach ($dataset as $key => $value) {
            $dateTime = new DateTime($value->year.'-'.$value->month.'-1', $timeZoneObj);
            $result[] = '[' . $dateTime->getTimestamp()*1000 . ', ' . $value->data . ']';
        }
        return implode(',', $result);
    }

    public function customersByMonth(): array
    {
        $dataset = DB::table($this->ordersTable)
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('year(created_at) as `year`'),
                    DB::raw('month(created_at) as `month`'),
                    DB::raw('monthname(created_at) as `monthname`'),
                    DB::raw('count(DISTINCT customer_id) as `data`')
                )
                ->whereYear("created_at", date("Y") )
                ->groupBy("year", "month", "monthname" )
                ->orderBy("year", "ASC")
                ->orderBy("month", "ASC")
                ->get()
                ->toArray();

        return [
            'byMonthMaxCount'   => max(array_column($dataset, 'data')),
            'dataset'           => $dataset
        ];
    }

    public function ordersByMonth(): array
    {
        $dataset = DB::table($this->ordersTable)
                ->whereNull('deleted_at')
                ->select(
                    DB::raw('year(created_at) as `year`'),
                    DB::raw('month(created_at) as `month`'),
                    DB::raw('monthname(created_at) as `monthname`'),
                    DB::raw('count(id) as `data`')
                )
                ->whereYear("created_at", date("Y") )
                ->groupBy("year", "month", "monthname" )
                ->orderBy("year", "ASC")
                ->orderBy("month", "ASC")
                ->get()
                ->toArray();

        return [
            'byMonthMaxCount'   => max(array_column($dataset, 'data')),
            'dataset'           => $dataset
        ];
    }

    public function salesByMonth(): array
    {
        $dataset = DB::table($this->ordersTable)
                ->whereNull('deleted_at')
                ->where('payment_state', PaidState::class)
                ->where('order_state_id', '<>', $this->cancelledStateId)
                ->select(
                    DB::raw('year(created_at) as `year`'),
                    DB::raw('month(created_at) as `month`'),
                    DB::raw('monthname(created_at) as `monthname`'),
                    DB::raw('sum(total_pre_payment) as `data`')
                )
                ->whereYear("created_at", date("Y") )
                ->groupBy("year", "month", "monthname" )
                ->orderBy("year", "ASC")
                ->orderBy("month", "ASC")
                ->get()
                ->toArray();
        return [
            'byMonthMaxCount'   => $this->getMaxAmount($dataset, 'data'),
            'dataset'           => $dataset
        ];
    }

    protected function getMaxAmount( $array, $key )
    {
        $max = 0;
        foreach( $array as $k => $v )
        {
            $max = max( array( $max, $v->{$key} / 100 ) );
        }
        return $max;
    }

    public function avgData(): array
    {
        $total_customers = DB::table($this->customersTable)
                    ->whereNull('deleted_at')
                    ->count();

        $total_paid_customers =  DB::table($this->ordersTable)
                    ->whereNull('deleted_at')
                    ->select(
                        DB::raw('count(DISTINCT customer_id) as `data`')
                    )
                    ->get()
                    ->toArray();

        return [
            'avg_sales' => round($this->grandTotal() / $this->count(), 2),
            'total_paid_customers' => $total_paid_customers[0]->data,
            'total_customers' => $total_customers,
        ];

    }
}
