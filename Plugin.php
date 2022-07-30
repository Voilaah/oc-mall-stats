<?php

namespace Voilaah\Mallstats;

use Backend\Facades\Backend;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Illuminate\Support\Facades\Event;

class Plugin extends PluginBase
{
    public $require = ['OFFLINE.Mall'];

    public function boot()
    {
        $this->registerExtensions();
    }
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function registerExtensions()
    {
        if (PluginManager::instance()->exists('OFFLINE.Mall')) {
            $this->extendOfflineMall();
        }
    }
    protected function extendOfflineMall()
    {
        // Add Analytics menu entry to Offline.Mall
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('OFFLINE.Mall', 'mall-orders', [
                'mall-statistics' => [
                    'label'       => 'voilaah.mallstats::lang.menu.statistics',
                    'url'         => \Backend::url('voilaah/mallstats/statistics'),
                    'icon'        => 'icon-area-chart',
                    'order'       => 900,
                    'permissions' => ['voilaah.mallstats.view_stats'],
                ],
                'mall-imports' => [
                    'label'       => 'voilaah.mallstats::lang.menu.import',
                    'url'         => \Backend::url('voilaah/mallstats/imports'),
                    'icon'        => 'icon-upload',
                    'order'       => 901,
                    'permissions' => ['voilaah.mallstats.import'],
                ],
                'mall-exports' => [
                    'label'       => 'voilaah.mallstats::lang.menu.export',
                    'url'         => \Backend::url('voilaah/mallstats/exports'),
                    'icon'        => 'icon-download',
                    'order'       => 902,
                    'permissions' => ['voilaah.mallstats.export'],
                ],
            ]);
        }, 5);
    }
}
