<?php namespace Voilaah\Mallstats\Models;

use Model;
use RainLab\User\Models\User;
use OFFLINE\Mall\Models\Customer;

/**
 * orders Model
 */
class CustomerExport extends \Backend\Models\ExportModel
{
    public $table = 'offline_mall_customers';

    public $belongsTo = [
        'user' => User::class,
    ];

    protected $appends  = [
        'customer_name',
        'customer_email',
    ];


    public function exportData($columns, $sessionKey = null)
    {
        $customers = self::make()->with([
                        'user',
                    ])
            ->get()
            ->toArray();

        return $customers;
    }

    public function getCustomerNameAttribute() {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getCustomerEmailAttribute() {
        if ($this->user)
            return $this->user->email;
        return "";
    }

}
