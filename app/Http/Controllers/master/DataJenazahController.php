<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DataJenazahController extends Controller
{
    public function index()
    {
        return view('pages.master.data_jenazah');
    }
}
