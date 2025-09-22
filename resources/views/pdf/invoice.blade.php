<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .header { display:flex; justify-content:space-between; }
    .totals { margin-top: 16px; width: 100%; }
    .totals td { padding: 4px; }
    .items-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
    .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .items-table th { background-color: #f2f2f2; }
    .address-section { margin: 16px 0; }
    .address-section h3 { margin-bottom: 8px; }
  </style>
</head>
<body>
  <div class="header">
    <div>
      <h2>Facture {{ $order->number }}</h2>
      <div>Date : {{ $order->created_at->format('d/m/Y') }}</div>
    </div>
    <div>
      <strong>KAMRI</strong><br/>
      Rue Exemple 1<br/>
      1000 Bruxelles<br/>
      Belgique
    </div>
  </div>

  <hr/>

  <div class="address-section">
    <h3>Adresse de facturation</h3>
    @php $bill = $order->billing_address ?? null; @endphp
    @if($bill)
      <div>{{ $bill['firstName'] ?? '' }} {{ $bill['lastName'] ?? '' }}</div>
      <div>{{ $bill['line1'] ?? '' }} {{ $bill['line2'] ?? '' }}</div>
      <div>{{ $bill['postalCode'] ?? '' }} {{ $bill['city'] ?? '' }}, {{ $bill['country'] ?? '' }}</div>
    @endif
  </div>

  <div class="address-section">
    <h3>Adresse de livraison</h3>
    @php $ship = $order->shipping_address ?? null; @endphp
    @if($ship)
      <div>{{ $ship['firstName'] ?? '' }} {{ $ship['lastName'] ?? '' }}</div>
      <div>{{ $ship['line1'] ?? '' }} {{ $ship['line2'] ?? '' }}</div>
      <div>{{ $ship['postalCode'] ?? '' }} {{ $ship['city'] ?? '' }}, {{ $ship['country'] ?? '' }}</div>
    @endif
  </div>

  <h3>Articles</h3>
  <table class="items-table">
    <thead>
      <tr>
        <th>Produit</th>
        <th>Variante</th>
        <th>Qté</th>
        <th>PU (€)</th>
        <th>Sous-total (€)</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($order->items as $line)
      <tr>
        <td>{{ $line->product_name }}</td>
        <td>{{ is_array($line->options) ? json_encode($line->options) : $line->options }}</td>
        <td>{{ $line->qty }}</td>
        <td>{{ number_format($line->unit_price, 2, ',', ' ') }}</td>
        <td>{{ number_format($line->subtotal, 2, ',', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <table class="totals">
    <tr><td align="right"><strong>Sous-total :</strong></td><td align="right">{{ number_format($order->subtotal, 2, ',', ' ') }} €</td></tr>
    @if($order->discount > 0)
    <tr><td align="right">Remise :</td><td align="right">- {{ number_format($order->discount, 2, ',', ' ') }} €</td></tr>
    @endif
    <tr><td align="right">Livraison :</td><td align="right">{{ number_format($order->shipping_price, 2, ',', ' ') }} €</td></tr>
    <tr><td align="right">TVA :</td><td align="right">{{ number_format($order->tax, 2, ',', ' ') }} €</td></tr>
    <tr><td align="right"><strong>Total TTC :</strong></td><td align="right"><strong>{{ number_format($order->total, 2, ',', ' ') }} €</strong></td></tr>
  </table>

  <p style="margin-top: 24px;">Merci pour votre commande 🙏</p>
</body>
</html>
