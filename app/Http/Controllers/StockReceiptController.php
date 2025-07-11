<?php

namespace App\Http\Controllers;

use App\Services\StockReceiptService;
use App\Http\Requests\StockReceipt\CreateStockReceiptRequest;
use App\Http\Requests\StockReceipt\ApproveStockReceiptRequest;
use App\Models\StockReceipt;
use Illuminate\Http\Request;

class StockReceiptController extends Controller
{
    protected $stockReceiptService;
    public function __construct(StockReceiptService $stockReceiptService){
        $this->stockReceiptService = $stockReceiptService;
    }
    public function addNewStockReceipt(CreateStockReceiptRequest $request){
        try{
            $data = $request->validated();
            $userId = auth()->id();
            $stock = $this->stockReceiptService->createStockReceipt($data, $userId);
            return response()->json([
                'message' =>'success',
                'stock' => $stock
            ]);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function approveStockReceipt(ApproveStockReceiptRequest $request, StockReceipt $stock){
        try{
            $user = auth()->user()->load('role');
            if (!in_array($user->role->name, ['Giám đốc'])) {
                return response()->json([
                    'message' => 'Bạn không có quyền duyệt phiếu.'
                ], 403);
            }
            $data = $request->validated();
            $receiptId = $stock->receipt_id;
            $approverId = auth()->id();
            $stock = $this->stockReceiptService->approveStockReceipt($receiptId, $approverId, $data['status']);
            return response()->json([
                'message'=> 'success',
                'stock'=> $stock
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message'=> $e->getMessage()
            ], 500);
        }
    }

    public function getAllStockReceipt(Request $request){
        try{
            $stock = $this->stockReceiptService->getAllStockReceipt($request->query());
            return response()->json([
                'message'=>'success',
                'stock'=> $stock
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message'=> $e->getMessage()
            ], 500);
        }
    }

    public function getAllStockReceiptStatus(){
        try{
            $defaultStatuses = ['chờ duyệt', 'đã duyệt', 'đã nhập', 'đã hủy'];
            $statusCount = $this->stockReceiptService->getAllStockReceiptStatus();
            $result = [];
            foreach ($defaultStatuses as $status) {
                $result[$status] = $statusCount[$status] ?? 0;
            }
            return response()->json([
                'message'=>'success',
                'statusCount' => $result
            ]);
        }catch(\Exception $e){
            return response()->json(['messafe'=>$e->getMessage()]);
        }
    }

    public function getStockReceipt(StockReceipt $stockReceipt){
        try{
            $stock = $this->stockReceiptService->getStockReceipt($stockReceipt);
            return response()->json([
                'message'=>'success',
                'stock'=> $stock
            ]);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()]);
        }
    }
}
