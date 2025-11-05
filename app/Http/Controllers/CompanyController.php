<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Http\Requests\StorecompanyRequest;
use App\Http\Requests\UpdatecompanyRequest;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company = Company::firstOrFail();
        return $this->success($company);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorecompanyRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatecompanyRequest $request, company $company)
    {
        $company = Company::firstOrFail();
        $company->update($request->validated());

        return $this->success($company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(company $company)
    {
        //
    }
}
