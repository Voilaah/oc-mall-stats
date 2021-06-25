<?php

namespace Voilaah\Mallstats\Classes;

use DB;
use Config;
use DateTime;
use DateTimeZone;
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

    public function byMonth(): array
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
}
