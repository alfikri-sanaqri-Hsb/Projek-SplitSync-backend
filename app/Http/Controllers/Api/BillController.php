<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $bill = Bill::create([
                'user_id' => auth()->id(),
                'title' => $request->title ?? 'Receipt OCR',
                'participants' => $request->participants ?? 1,
                'total_price' => collect($request->items)->sum('price'),
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {

                $bill->items()->create([
                    'item_name' => $item['item_name'] ?? 'Unknown Item',
                    'price' => $item['price'] ?? 0,
                    'participant_name' => $item['participant_name'] ?? '',
                    'payment_status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $bill->load([
                    'items',
                    'user'
                ])
            ]);
        });
    }

    public function index()
    {
        $bills = Bill::with([
                'items',
                'user'
            ])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bills
        ]);
    }

    public function show($id)
    {
        $bill = Bill::with([
                'items',
                'user'
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $bill
        ]);
    }

    public function updateItemStatus(Request $request, $itemId)
    {
        $item = BillItem::findOrFail($itemId);

        $item->payment_status = $request->status;
        $item->save();

        $bill = $item->bill;

        $allCompleted = $bill->items()
            ->where('payment_status', 'pending')
            ->count() === 0;

        $bill->status = $allCompleted
            ? 'completed'
            : 'pending';

        $bill->save();

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    public function clearHistory(Request $request)
    {
        $user = $request->user();

        $user->bills()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat transaksi berhasil dihapus'
        ]);
    }
}