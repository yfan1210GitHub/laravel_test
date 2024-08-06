<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    //
    public function __construct(){
        // $this->middleware(['auth', 'role:Super Admin|Team Lead']);
    }
    protected $companyRepository, $employeeRepository;

    // public function __construct(CompanyRepository $companyRepository, EmployeeRepository $employeeRepository)
    // {
    //     $this->companyRepository = $companyRepository;
    //     $this->employeeRepository = $employeeRepository;
    // }

    public function index(Request $request)
    {
        $paginate = 10; // You might want to make this configurable
        $company = Company::latest()->paginate($paginate);
        return CompanyResource::collection($company);
    }

    public function store(CompanyRequest $request)
    {
      
        DB::beginTransaction();
        try {
            $company = new Company();
            $company->name        = $request->name;
            $company->email       = $request->email;
            $company->address     = $request->address;
            $company->website     = $request->website;
            $company->logo        = $request->logo;
            $company->status      = $request->status;
            $company->contact_num = $request->contact_num;
            $company->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return response()->json(new CompanyResource($company), 200);
    }

    public function update(CompanyRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $company = Company::findOrFail($id);
            $company->name        = $request->name;
            $company->email       = $request->email;
            $company->address     = $request->address;
            $company->website     = $request->website;
            $company->logo        = $request->logo;
            $company->status      = $request->status;
            $company->contact_num = $request->contact_num;
            $company->save();
            # Update company
            // $company = $this->companyRepository->update((Array) $request, $id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return response()->json(new CompanyResource($company), 200);
    }

    public function show(Request $request, $id)
    {
        return $this->response(new CompanyResource($this->companyRepository->findOrFail($id)));
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $company = Company::findOrFail($id); // Find the employee by ID or fail
            $company->delete(); // Delete the employee
    
            DB::commit();
            
            // Return a successful response with a message
            return response()->json(['message' => 'Company deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to delete company'], 500);
        }
    }
}
