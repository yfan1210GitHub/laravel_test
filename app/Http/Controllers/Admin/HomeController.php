<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportVoucherFile;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\ExportVoucher;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\v1\OrderManagement\VoucherRequest;
use App\Http\Resources\Admin\v1\VoucherResource;
use App\Http\Resources\Admin\v1\ExportVoucherResource;
use App\Repositories\VoucherRepository;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{

    protected $voucherRepository;

    public function __construct(VoucherRepository $voucherRepository)
    {
        $this->voucherRepository = $voucherRepository;
    }

    //
    public function store(VoucherRequest $request)
    {
        DB::beginTransaction();
        try {
            $vouchers = collect();
            # Get voucher request body and merge created by and issue type
            $body = $request->only([
                'code', 'description', 'amount', 'minimum_amount_to_apply', 'maximum_discount_amount',
                'voucher_discount_type', 'maximum_use_per_user', 'start_at', 'expired_at', 'vendor_id', 'product_categories', 'products'
            ]) + [
                'created_by' => Auth::user()->id,
                'voucher_type' => $request->vendor_id ? VoucherType::VENDOR : VoucherType::GLOBAL
            ];

            // $is_bulk_create1 = false;
            $is_bulk_create = $request->voucher_count > 1 ? true : false;

            # Loop and create for each selected vendor
            for ($i = 1; $i <= $request->voucher_count; $i++) {
                $voucher = $this->voucherRepository->storeVoucher((array) $body, $is_bulk_create);
                $vouchers->push($voucher);
            }


            if($is_bulk_create){
                $export = new ExportVoucherFile($vouchers);
                // Set a unique filename for the export
                $filename = 'voucher_export_' . now()->format('Ymd_His') . '.xlsx';
                $filePath = config('app.env').'/exports/' . $filename;
                // Store the export in S3
                Excel::store($export, $filePath, 's3');
                // Get the public URL of the stored file
                $export_voucher = new ExportVoucher();
                $export_voucher->code = $request->code;
                $export_voucher->link = $filePath;
                $export_voucher->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        return $this->response(VoucherResource::collection($vouchers));
    }

    public function update(VoucherRequest $request, $id)
    {
        # Get voucher request body and merge created by and issue type
        $body = $request->only([
            'description', 'amount', 'minimum_amount_to_apply', 'maximum_discount_amount',
            'voucher_discount_type', 'maximum_use_per_user', 'start_at', 'expired_at', 'vendor_id', 'product_categories', 'products'
        ]) + [
            'code' => $request->code,
            'voucher_type' => $request->vendor_id ? VoucherType::VENDOR : VoucherType::GLOBAL
        ];

        # Loop and create for each selected vendor
        DB::beginTransaction();
        try {
            $voucher = $this->voucherRepository->update($body, $id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $this->response(new VoucherResource($voucher));
    }

    public function show(Request $request, $id)
    {
        return $this->response(new VoucherResource($this->voucherRepository->findOrFail($id)->loadRelationship()));
    }

    public function index(Request $request)
    {
        return $this->paginateResponse($this->voucherRepository->paginate(), VoucherResource::class);
    }

    public function destroy(Request $request, $id)
    {
        return $this->response($this->voucherRepository->destroy($id));
    }

    public function toggle($id)
    {
        return $this->response(new VoucherResource($this->voucherRepository->toggle($id)->loadRelationship()));
    }

    public function exportIndex(){
        return $this->paginateResponse(ExportVoucher::paginate(), ExportVoucherResource::class);
    }
}
