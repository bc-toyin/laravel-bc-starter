<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getAppClientId() {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_client_id');
        } else {
            return config('bigcommerce.bc_app_client_id');
        }
    }
}
