<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\UpdatecompanyRequest;
use App\Helpers\ApiResponse;

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

            return ApiResponse::success($company);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update company data', 500, $e->getMessage());
        }
    }
}
