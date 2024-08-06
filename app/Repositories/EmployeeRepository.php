<?php

namespace App\Repositories;

use App\Models\Company;
use App\Models\Employee;
use DB;

class EmployeeRepository extends BaseRepository
{

    protected $dataArray = [
        'first_name',
        'last_name',
        'email',
        'company_id',
        'status',
        'contact_num',
    ];

    public function model()
    {
        return Employee::class;
    }

    public function updateOrCreate(Array $request, Company $company)
    {
        DB::beginTransaction();
        try {
            $body = [
                'company_id' => $company->id,
                'sort_order' => $request['sort_order'] ?? 0
            ];

            # Check exist 'id' and is belongs to this option
            if (isset($request['id']) && $option->optionValues()->find($request['id'])) {
                $option_value = $this->update($body, $request['id']);
            } else {
                $option_value = $this->store($body);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $option_value;
    }
}
