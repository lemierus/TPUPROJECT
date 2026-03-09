<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DataMakamController extends Controller
{
    public function index()
    {
        return view('pages.master.data_makam');
    }
}
