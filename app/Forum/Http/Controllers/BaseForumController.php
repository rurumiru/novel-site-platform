<?php

namespace App\Forum\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class BaseForumController extends Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
