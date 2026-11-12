<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XenditInvoiceService
{
    public function createInvoice(Order $order, User $customer, ?string $paymentMethod = null): array
    {
        $secretKey = config('services.xendit.secret_key');
        if (!$secretKey) {
            throw new RuntimeException('Xendit is not configured.');
        }

        $paymentMethods = match ($paymentMethod) {
            'qris' => ['QRIS'],
            'virtual_account' => ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI', 'BNC'],
            'credit_card' => ['CREDIT_CARD'],
            default => null,
        };

        $payload = [
            'external_id' => $order->invoice_number,
            'amount' => (int) round((float) $order->total_amount),
            'currency' => 'IDR',
            'payer_email' => $customer->email,
            'description' => 'Pemesanan paket KawalBelajar ' . $order->invoice_number,
            'invoice_duration' => 86400,
        ];
        if ($paymentMethods !== null) $payload['payment_methods'] = $paymentMethods;

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post('https://api.xendit.co/v2/invoices', $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Xendit invoice request failed.');
        }

        $invoice = $response->json();
        if (empty($invoice['id']) || empty($invoice['invoice_url'])) {
            throw new RuntimeException('Xendit returned an incomplete invoice response.');
        }

        return [
            'invoice_id' => $invoice['id'],
            'payment_url' => $invoice['invoice_url'],
        ];
    }
}
