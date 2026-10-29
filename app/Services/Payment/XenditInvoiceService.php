<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class XenditInvoiceService
{
    public function createInvoice(Order $order, User $customer): array
    {
        $secretKey = config('services.xendit.secret_key');
        if (!$secretKey) {
            throw new RuntimeException('Xendit is not configured.');
        }

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->timeout(15)
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $order->invoice_number,
                'amount' => (int) round((float) $order->total_amount),
                'currency' => 'IDR',
                'payer_email' => $customer->email,
                'description' => 'Pemesanan paket KawalBelajar ' . $order->invoice_number,
                'invoice_duration' => 86400,
            ]);

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
