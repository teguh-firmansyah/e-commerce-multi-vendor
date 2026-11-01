<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $shop = $request->user()?->shop;

        return Inertia::render('Dashboard', [
            'shop' => $shop === null ? null : [
                'name' => $shop->name,
                'status' => $shop->status->value,
                'reason' => $shop->status_reason,
            ],
        ]);
    }
}
