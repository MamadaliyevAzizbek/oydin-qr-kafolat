<?php

namespace App\Http\Controllers;

class PanelController extends Controller
{
    public function __invoke()
    {
        return view('panel');
    }
}
