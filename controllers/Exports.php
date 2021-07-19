<?php namespace Voilaah\Mallstats\Controllers;

use BackendMenu;
use Backend\Classes\Controller;


/**
 * Orders Back-end Controller
 */
class Exports extends Controller
{
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-exports');
    }

    public function index()
    {
        // parent::index();
        $this->pageTitle = 'voilaah.mallstats::lang.menu.exports';
    }
}
