<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function render(Order $order)
    {
        $order->load('items');
        return Pdf::loadView('pdf.invoice', ['order' => $order]);
    }
}
