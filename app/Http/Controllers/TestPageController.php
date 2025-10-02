<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class TestPageController extends Controller
{
    /**
     * Display the custom test page.
     */
    public function __invoke(): View
    {
        return view('custom.test');
    }
}
