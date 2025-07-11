<?php

namespace App\Services;

use App\Models\StockReceipt;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockReceiptService
{
    public static function createStockReceipt(array $data, $userId){
        DB::beginTransaction();
        try{
            $product = Product::where('sku', $data['sku'])->firstOrFail();
            $receipt = new StockReceipt();
            $receipt->branch_id = $data['branch_id'];
            $receipt->user_id = $userId;
            $receipt->supplier_id = $data['supplier_id'];
            $receipt->note = $data['note'] ?? null;
            $receipt->product_id = $product->prod_id;
            $receipt->sku = $product->sku;
            $receipt->qty = $data['qty'];
            $receipt->unit_price = $data['unit_price'];
            $receipt->total_cost = $data['unit_price'] * $data['qty'];
            $receipt->approved_by = null;
            $receipt->received_at = null;
            $receipt->save();
            DB::commit();
            return $receipt;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public static function approveStockReceipt(int $receiptId, int $approverId, string $newStatus){
        DB::beginTransaction();
        try{
            $receipt = StockReceipt::with('product')->findOrFail($receiptId);
            $current = $receipt->status;
            $allowedTransitions = [
                'chờ duyệt'=> ['đã duyệt', 'đã hủy'],
                'đã duyệt'=> ['đã nhập'],
            ];
            if(!isset($allowedTransitions[$current])|| !in_array($newStatus, $allowedTransitions[$current])){
                throw new \Exception("Không thể chuyển trạng thái từ \"$current\" sang \"$newStatus\".");
            }
            if($newStatus === 'đã nhập'){
                $product = $receipt->product;
                $product->stock_qty += $receipt->qty;
                $product->save();
                $receipt->received_at = now();
            }
            $receipt->status = $newStatus;
            $receipt->approved_by = $approverId;
            $receipt->save();
            DB::commit();
            return $receipt;
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getAllStockReceipt($filters = []){
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] :20;
        $query = StockReceipt::query()
            ->with(['product:prod_id,name,sku', 'user:id,name', 'branch:branch_id,name']);
        if(!empty($filters['keyword'])){
            $query->where('receipt_code','like','%'. $filters['keyword']. '%')
                ->orWhere('sku', 'like','%'. $filters['keyword']. '%');
        }
        if(!empty($filters['status'])){
            $query->where('status', 'like','%'. $filters['status']. '%');
        }
        if(!empty($filters['branch_id'])){
            $query->where('branch_id','like','%'. $filters['branch_id']);
        }
        if(!empty($filters['created_at'])){
            $query->whereDate('created_at','=', $filters['created_at']);
        }
        return $query->orderBy('created_at')->paginate($perPage)->appends($filters);
    }

    public static function getAllStockReceiptStatus(){
        return StockReceipt::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public static function getStockReceipt(StockReceipt $stockReceipt){
        return $stockReceipt->load([
            'product:prod_id,name,sku',
            'user:id,name',
            'branch:branch_id,name',
            'supplier:supp_id,name',
            'approver:id,name'
        ]);
    }
}
