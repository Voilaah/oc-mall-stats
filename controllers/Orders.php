<?php namespace Voilaah\Mallstats\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Orders Back-end Controller
 */
class Orders extends Controller
{
    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        'Backend.Behaviors.ImportExportController'
    ];

    /**
     * @var string Configuration file for the `FormController` behavior.
     */
    public $importExportConfig = 'config_import_export.yaml';


    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-exports');
    }

}
