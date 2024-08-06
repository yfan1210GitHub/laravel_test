<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Repositories\EmployeeRepository;
use App\Http\Resources\EmployeeResource;
use Illuminate\Http\Request;
use DB;

use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{

    // protected $employeeRepository;

    public function __construct(){
        // $this->middleware(['auth', 'role:Super Admin|Team Lead']);
    }

    // public function __construct(EmployeeRepository $employeeRepository)
    // {
    //     $this->employeeRepository = $employeeRepository;
    // }


    public function index(Request $request)
    {
        log::info("hereeeeeeee");
        $paginate = 10;
        $employees = Employee::with('company')->latest()->paginate($paginate);
        // return EmployeeResource::collection($employees);
        return response()->json(EmployeeResource::collection($employees), 200);
    }
    
    public function store(EmployeeRequest $request)
    {
        DB::beginTransaction();
        try {
            $employee = new Employee();
            $employee->first_name  = $request->first_name;
            $employee->last_name   = $request->last_name;
            $employee->email       = $request->email;
            $employee->company_id  = $request->company_id;
            $employee->status      = $request->status;
            $employee->contact_num = $request->contact_num;
            $employee->save();
           
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        $employee->load('company');
        return response()->json(new EmployeeResource($employee), 200);

        // return $this->response(new EmployeeResource($employee));
    }

    public function update(EmployeeRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($id);
            $employee->first_name  = $request->first_name;
            $employee->last_name   = $request->last_name;
            $employee->email       = $request->email;
            $employee->company_id  = $request->company_id;
            $employee->status      = $request->status;
            $employee->contact_num = $request->contact_num;
            $employee->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return response()->json(new EmployeeResource($employee), 200);
    }

    public function show(Request $request, $id)
    {
        log::info("showwoing");
        $employee = Employee::findOrFail($id);
        $employee->load('company');
        return response()->json(new EmployeeResource($employee), 200);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($id); // Find the employee by ID or fail
            $employee->delete(); // Delete the employee
    
            DB::commit();
            
            // Return a successful response with a message
            return response()->json(['message' => 'Employee deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to delete employee'], 500);
        }
    }
}
