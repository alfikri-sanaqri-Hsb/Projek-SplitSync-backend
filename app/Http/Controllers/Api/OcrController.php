<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OCRController extends Controller
{
    public function scanReceipt(Request $request)
    {
        try {
            $request->validate([
                'receipt' => 'required|image'
            ]);

            $image = $request->file('receipt');
            $path = $image->getPathname();

            $text = (new TesseractOCR($path))
                ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
                ->lang('eng')
                ->config('tessedit_pageseg_mode', '6')
                ->run();

            logger($text);

            $lines = explode("\n", $text);
            $items = [];


            $blockedWords = [
                'SUBTOTAL', 'SERVICE', 'TAX', 'DISC', 'DISCOUNT', 
                'TABLE', 'PAX', 'POS', 'RCPT', 'RECEIPT', 'EMAIL', 
                'BEVERAGES', 'FOODS', 'CASH', 'CHANGE', 'BANK', 
                'DEBIT', 'CREDIT', 'BILL', 'AMOUNT', 'AVENUE', 
                'MALL', 'GROUND', 'FLOOR', 'UNION', 'WELCOME', 'REPT'
            ];

            foreach ($lines as $line) {
                $line = trim($line);

                if (empty($line)) {
                    continue;
                }

                $line = preg_replace('/\s+/', ' ', $line);


                $isBlocked = false;
                foreach ($blockedWords as $word) {
                    if (str_contains(strtoupper($line), $word)) {
                        $isBlocked = true;
                        break;
                    }
                }


                if (preg_match('/TOTAL/i', $line) && !preg_match('/^[1-9]\s/', $line)) {
                    continue;
                }

                if ($isBlocked) {
                    continue;
                }


                if (!preg_match('/^[1-9]/', $line)) {
                    continue;
                }


                $price = 0;
                $cleanedLineForName = $line;

                if (preg_match('/(\d[\d.,\s]*)$/', $line, $matches)) {
                    $rawPrice = $matches[1];
                    $price = (int) preg_replace('/[^0-9]/', '', $rawPrice);

                    if ($price > 2000000) { // Batasi angka yang tidak masuk akal
                        $price = 0;
                    } else {

                        $cleanedLineForName = str_replace($rawPrice, '', $line);
                    }
                }

                $itemName = preg_replace('/^\d+\s*/', '', $cleanedLineForName);
                $itemName = trim($itemName);

                if (!preg_match('/[A-Za-z]/', $itemName)) {
                    continue;
                }

                if (strlen($itemName) < 3) {
                    continue;
                }

                if (preg_match('/\d{7,}/', $itemName)) {
                    continue;
                }

                $items[] = [
                    "item_name" => $itemName,
                    "price" => $price,
                    "participant_name" => "",
                    "payment_status" => "pending"
                ];
            }

            $total = 0;
            if (preg_match('/TOTAL.*?(\d[\d.,]+)/i', $text, $totalMatch)) {
                $total = (int) preg_replace('/[^0-9]/', '', $totalMatch[1]);
            }

            if ($total <= 0) {
                $total = collect($items)->sum('price');
            }

            logger($items);

            return response()->json([
                'success' => true,
                'data' => [
                    'raw_text' => $text,
                    'title' => 'Hasil Scan Receipt',
                    'items' => $items,
                    'total' => $total
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}