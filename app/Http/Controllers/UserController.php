<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if($request->has('id')) {
            return User::where('id', $request->query('id'))->firstOrFail();
        }
        return User::all();
    }
}
