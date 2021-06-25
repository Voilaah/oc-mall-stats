<?php namespace Voilaah\Mallstats\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use OFFLINE\Mall\Classes\Utils\Money;
use Voilaah\Mallstats\Classes\MallStats;
use OFFLINE\Mall\Classes\Stats\OrdersStats;

/**
 * Statistics Back-end Controller
 */
class Statistics extends Controller
{
      public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-statistics');
    }


    public function index()
    {
        // parent::index();
        $this->pageTitle = 'voilaah.mallstats::lang.menu.statistics';
        $this->addCss('/plugins/voilaah/mallstats/assets/css/statistics.css');

        // inherit from Mall controllers Orders
        $this->addCss('/plugins/offline/mall/assets/backend.css');
        $this->vars['stats'] = new OrdersStats();
        $this->vars['money'] = app(Money::class);

        // Our Custom Mall Statistics
        $this->vars['mallstats'] = new MallStats();

    }
}
