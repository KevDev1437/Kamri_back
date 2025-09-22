<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $req)
    {
        $user = $req->user();
        $q = Order::with('items')->where('user_id', $user->id);

        if ($term = $req->query('q')) {
            $q->where('number', 'like', "%{$term}%");
        }
        if ($status = $req->query('status')) {
            $q->where('status', $status);
        }
        if ($from = $req->query('date_from')) {
            $q->whereDate('created_at', '>=', $from);
        }
        if ($to = $req->query('date_to')) {
            $q->whereDate('created_at', '<=', $to);
        }

        $perPage = (int) $req->query('perPage', 10);
        $page = (int) $req->query('page', 1);

        $paginator = $q->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'items' => OrderResource::collection($paginator->items()),
            'total' => $paginator->total(),
        ]);
    }

    public function show(Request $req, Order $order)
    {
        $order->load('items');
        $this->authorize('view', $order);
        return new OrderResource($order);
    }

    public function invoice(Request $req, Order $order, InvoiceService $invoice)
    {
        $this->authorize('view', $order);
        $pdf = $invoice->render($order);
        $filename = 'facture-' . $order->number . '.pdf';

        return $pdf->download($filename);
    }

    public function reorder(Request $req, Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items');

        $items = $order->items->map(fn($l) => [
            'product_id' => $l->product_id,
            'name' => $l->product_name,
            'price' => (float) $l->unit_price,
            'qty' => (int) $l->qty,
            'variant' => $l->options,
            'image' => $l->product_image,
        ])->values();

        return response()->json([
            'success' => true,
            'message' => 'Articles prêts à être ajoutés au panier',
            'items' => $items,
        ]);
    }
}
