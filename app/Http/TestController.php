<?php

namespace App\Http;

use Illuminate\Http\Request;
use Laracord\Http\Controllers\Controller;
use Laracord\Laracord;

class TestController extends Controller
{
    public function __construct(Laracord $bot)
    {
    }
}
