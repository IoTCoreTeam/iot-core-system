<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UpdateuserRequest;
use App\Helpers\ApiResponse;
use App\Helpers\SystemLogHelper;

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
            SystemLogHelper::log('user.delete.success', 'User deleted successfully', [
                'user_id' => $id,
            ]);
            return ApiResponse::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            SystemLogHelper::log('user.delete.failed', 'Failed to delete user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);
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
            SystemLogHelper::log('user.update.success', 'User updated successfully', [
                'user_id' => $user->id,
            ]);

            return ApiResponse::success($user, 'User updated successfully');
        } catch (\Exception $e) {
            SystemLogHelper::log('user.update.failed', 'Failed to update user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);
            return ApiResponse::error('Failed to update user', 500, $e->getMessage());
        }
    }

    public function filter(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 5);
            $filters = $request->input('filters', []);

            $users = User::filterUsers($filters, $perPage);

            return response()->json($users);
        } catch (\Exception $e) {
            SystemLogHelper::log('user.filter.failed', 'Failed to filter users', [
                'error' => $e->getMessage(),
            ], ['level' => 'error']);
            return ApiResponse::error('Failed to filter users', 500, $e->getMessage());
        }
    }
}
