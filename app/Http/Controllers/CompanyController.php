<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\UpdatecompanyRequest;
use App\Helpers\ApiResponse;
use App\Helpers\SystemLogHelper;

class CompanyController extends Controller
{

    public function index()
    {
        try {
            $company = Company::firstOrFail();
            return ApiResponse::success($company);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch company data', 500, $e->getMessage());
        }
    }
    public function update(UpdatecompanyRequest $request, company $company)
    {
        try {
            $company = Company::firstOrFail();
            $company->update($request->validated());
            SystemLogHelper::log('company.update.success', 'Company information updated successfully', [
                'company_id' => $company->id,
            ]);

            return ApiResponse::success($company);
        } catch (\Exception $e) {
            SystemLogHelper::log('company.update.failed', 'Failed to update company information', [
                'company_id' => isset($company) ? $company->id : null,
                'error' => $e->getMessage(),
            ], ['level' => 'error']);
            return ApiResponse::error('Failed to update company data', 500, $e->getMessage());
        }
    }
}
