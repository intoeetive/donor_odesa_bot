<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use DefStudio\Telegraph\Models\TelegraphBot;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function index()
    {
        /** @var DefStudio\Telegraph\Models\TelegraphBot $telegraphBot */

        $telegraphBot = TelegraphBot::find(5);
        $info = $telegraphBot->info();

        var_dump($info);
    }
}
