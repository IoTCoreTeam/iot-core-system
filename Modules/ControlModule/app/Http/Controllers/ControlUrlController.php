<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Http\Requests\StoreControlUrlRequest;
use Modules\ControlModule\Http\Requests\UpdateControlUrlRequest;
use Modules\ControlModule\Models\ControlUrl;
use Modules\ControlModule\QueryBuilders\ControlUrlQueryBuilder;
use Modules\ControlModule\Services\ControlUrlService;
use Throwable;

class ControlUrlController extends Controller
{
    public function __construct(private readonly ControlUrlService $controlUrlService) {}

    public function index(Request $request)
    {
        return ControlUrlQueryBuilder::fromRequest($request);
    }

    public function store(StoreControlUrlRequest $request)
    {
        $payload = $request->validated();

        try {
            $result = $this->controlUrlService->create($payload);

            return ApiResponse::success($result['control_url'], $result['message'], $result['status']);
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('control_url.creation_failed', $e->getMessage(), ['payload' => $payload,], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to create control url';

            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function update(UpdateControlUrlRequest $request, string $id)
    {
        $payload = $request->validated();

        try {
            $result = $this->controlUrlService->update($id, $payload);

            return ApiResponse::success($result['control_url'], $result['message']);
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('control_url.update_failed', $e->getMessage(), ['control_url_id' => $id,'payload' => $payload,], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to update control url';

            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function delete(string $id)
    {
        try {
            $this->controlUrlService->delete($id);

            return ApiResponse::success(null, 'Control url deleted successfully');
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('control_url.deletion_failed', $e->getMessage(), ['control_url_id' => $id,], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to delete control url';

            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function executeControlUrl(Request $request, string $id)
    {
        try {
            $payload = $request->all();
            $result = $this->controlUrlService->execute($id, $payload);

            return ApiResponse::success($result['control_url'], $result['message'], $result['status']);
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('control_url.execution_failed', $e->getMessage(), ['control_url_id' => $id,], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to execute control url';

            return ApiResponse::error($errorMessage, 500);
        }
    }
}
