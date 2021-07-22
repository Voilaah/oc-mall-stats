<?php namespace Voilaah\MallStats\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Product Import Export Back-end Controller
 */
class Products extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.ImportExportController'
    ];

    public $importExportConfig = 'config_import_export.yaml';


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', $this->action);
    }
}
