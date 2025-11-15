<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateuserRequest;
use App\Helpers\ApiResponse;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // phân trang cho dữ liệu nguời dùng
        $perPage = $request->integer('per_page', 5);

        if ($request->has('id')) {
            return User::where('id', $request->query('id'))->firstOrFail();
        }
        if ($request->has('search')) {
            $keyword = $request->query('search');
            return User::search($keyword)->paginate($perPage);
        }
        return User::paginate($perPage);
    }

    public function destroy($id)
    {
        try{
            User::destroy($id);
            return ApiResponse::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete user', 500, $e->getMessage());
        }
    }

    public function update(UpdateuserRequest $request, $id)
    {
        try {
            $user = User::find($id);
            if (! $user) {
                return ApiResponse::error('User not found', 404);
            }
            $data = $request->validated();
            if (! array_key_exists('password', $data)) {
                unset($data['password']);
            }

            $user->fill($data);
            $user->save();

            return ApiResponse::success($user, 'User updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update user', 500, $e->getMessage());
        }
    }
}
