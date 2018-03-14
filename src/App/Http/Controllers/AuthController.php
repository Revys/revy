<?php

namespace Revys\Revy\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
	public function login()
	{
		return \View::make('auth.login');
	}
}
