<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // phân trang cho dữ liệu nguời dùng
        $perPage = $request->integer('per_page', 5);
        
        if($request->has('id')) {
            return User::where('id', $request->query('id'))->firstOrFail();
        }
        if ($request->has('search')) {
            $keyword = $request->query('search');
            return User::search($keyword)->paginate($perPage);
        }
        return User::paginate($perPage);
    }
}
