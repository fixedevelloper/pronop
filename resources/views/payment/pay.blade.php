<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #020617, #0F172A, #1E293B);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .card {
            background: #0F172A;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            animation: fadeIn 0.4s ease-in-out;
        }

        .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #22C55E;
            margin: 15px 0;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-pay {
            background: linear-gradient(90deg, #22C55E, #16A34A);
            color: white;
        }

        .btn-cancel {
            background: #1E293B;
            color: #CBD5F5;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .ref {
            margin-top: 15px;
            font-size: 12px;
            color: #64748B;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="card">

    <div class="icon">💳</div>

    <h2>Paiement de la commande</h2>

    <div class="amount">
        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
    </div>

    <p>Confirmez votre paiement sécurisé</p>

    <!-- 🔥 PAYER -->
    <form method="GET" action="{{ url('/payment/success/'.$payment->id) }}">
        <button type="submit" class="btn btn-pay">
            ✅ Payer maintenant
        </button>
    </form>

    <!-- ❌ ANNULER -->
    <form method="GET" action="{{ url('/payment/cancel/'.$payment->id) }}">
        <button type="submit" class="btn btn-cancel">
            ❌ Annuler
        </button>
    </form>

    <div class="ref">
        Référence : #{{ $payment->reference }}
    </div>

</div>

</body>
</html>
