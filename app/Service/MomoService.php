<?php

namespace App\Service;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Http;

class MomoService
{
    private $baseUrl;
    private $subscriptionKey;
    private $apiUser;
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.momo.base_url');
        $this->subscriptionKey = config('services.momo.subscription_key');
        $this->apiUser = config('services.momo.api_user');
        $this->apiKey = config('services.momo.api_key');
    }

    // 🔹 Obtenir un token d'accès
    public function getToken()
    {

        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'Authorization' => 'Basic ' . base64_encode($this->apiUser . ':' . $this->apiKey),
        ])->post($this->baseUrl . '/token/');

        return $response['access_token'] ?? null;
    }


       public function requestToPay($referenceId, $phone, $amount, $currency = 'XAF')
        {
            $token = $this->getToken();
            logger($token);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Reference-Id' => $referenceId,
                'X-Target-Environment' => config('services.momo.env', 'mtncameroon'),
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Content-Type' => 'application/json',
                'X-Callback-Url'=>route('momo.callback')
            ])->post($this->baseUrl . '/v1_0/requesttopay', [
                'amount' => $amount,
                'currency' => config('services.momo.currency', 'XAF'),
                'externalId' => $referenceId,
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => '237'.$phone
                ],
                'payerMessage' => 'Paiement commande',
                'payeeNote' => 'Merci pour votre achat'
            ]);
            logger('✅ Réponse MoMo reçue', [$response->status()]);
            logger(json_encode($response->body()));
            return $response->status();
        }

    // 🔹 Vérifier le statut d’un paiement
    public function getPaymentStatus($referenceId)
    {
        $token = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Target-Environment' => config('services.momo.env', 'sandbox'),
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
        ])->get($this->baseUrl . '/v1_0/requesttopay/' . $referenceId);

        return $response->json();
    }
}
