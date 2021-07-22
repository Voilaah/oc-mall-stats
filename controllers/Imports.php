<?php namespace Voilaah\Mallstats\Controllers;

use BackendMenu;
use Backend\Classes\Controller;


/**
 * Orders Back-end Controller
 */
class Imports extends Controller
{
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-imports');
    }

    public function index()
    {
        // parent::index();
        $this->pageTitle = 'voilaah.mallstats::lang.menu.imports';
    }
}
