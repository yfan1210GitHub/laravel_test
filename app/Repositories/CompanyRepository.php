<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Models\Company;
use DB;
use Illuminate\Support\Str;

class CompanyRepository extends BaseRepository
{

    protected $dataArray = [
        'name',
        'email',
        'website',
        'address',
        'logo',
        'status',
        'contact_num',
    ];

    public function model()
    {
        return Company::class;
    }

    public function destroyEmployee($id)
    {
        $record = $this->findOrFail($id);

        DB::beginTransaction();
        try {

            $record->employee()->delete();
            $record->delete();

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }
}
