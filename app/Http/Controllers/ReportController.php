<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Customer;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;
    
        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])
            ->with(['items.product', 'customer'])
            ->get();
    
        $data = $orders->map(function ($order) {
            $customerName = $order->customer ? $order->getCustomerName() : 'Walk-in Customer';
            $orderItems = $order->items->map(function ($orderItem) {
                return [
                    'product' => $orderItem->product->name,
                    'quantity' => $orderItem->quantity,
                    'price' => number_format($orderItem->price, 2), // format the price to 2 decimals
                ];
            });
    
            return [
                'customer' => $customerName,
                'order_items' => $orderItems,
                'total' => number_format($order->total(), 2), // format the total to 2 decimals
            ];
        });
    
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
    
        // Table header
        $pdf->Cell(60,10,'Customer',1);
        $pdf->Cell(60,10,'Product',1);
        $pdf->Cell(30,10,'Quantity',1);
        $pdf->Cell(30,10,'Price',1);
        $pdf->Ln();  // New line
    
        // Table body
        $totalPrice = 0;
        foreach($data as $item) {
            $pdf->SetFont('Arial','',12);
            foreach($item['order_items'] as $orderItem) {
                $pdf->Cell(60,10,$item['customer'],1);
                $pdf->Cell(60,10,$orderItem['product'],1);
                $pdf->Cell(30,10,$orderItem['quantity'],1);
                $pdf->Cell(30,10,$orderItem['price'],1);
                $pdf->Ln();  // New line
    
                // Calculate total price
                $totalPrice += $orderItem['price'];
            }
        }
    
        // Total row
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(150,10,'Total',1);
        $pdf->Cell(30,10,$totalPrice,1);
        $pdf->Ln();  // New line
        $content = $pdf->Output('report.pdf', 'S');  // get content as a string
        
        return response($content)->header('Content-Type', 'application/pdf');
    }
    
}