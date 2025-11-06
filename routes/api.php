    <?php

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\CompanyController;
    use App\Http\Controllers\UserController;

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:api');

    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('register', [AuthController::class, 'register']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/company', [CompanyController::class, 'index']);
        Route::put('/company', [CompanyController::class, 'update']);
    });
