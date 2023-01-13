<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Services\Event\Service;

class BaseController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}