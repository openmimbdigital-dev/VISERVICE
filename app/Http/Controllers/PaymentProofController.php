<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionInvoice;
use App\Support\PaymentProofStorage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    /**
     * Entrega el comprobante de pago de una factura de suscripción.
     *
     * El archivo vive en almacenamiento privado: solo lo ve quien administra
     * las suscripciones.
     */
    public function __invoke(SubscriptionInvoice $subscriptionInvoice): StreamedResponse
    {
        abort_unless(auth()->user()?->hasRole('superAdmin'), 403);

        abort_unless(PaymentProofStorage::exists($subscriptionInvoice->payment_proof), 404);

        return Storage::disk(PaymentProofStorage::DISK)->response(
            (string) $subscriptionInvoice->payment_proof
        );
    }
}
