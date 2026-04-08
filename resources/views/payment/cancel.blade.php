<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement échoué</title>
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
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            animation: fadeIn 0.5s ease-in-out;
        }

        .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #EF4444, #DC2626);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }

        h2 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        p {
            color: #94A3B8;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 14px 20px;
            border-radius: 12px;
            background: linear-gradient(90deg, #EF4444, #DC2626);
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            margin-bottom: 10px;
        }

        .btn-secondary {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 12px;
            background: #1E293B;
            color: #CBD5F5;
            text-decoration: none;
            font-size: 14px;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .small {
            margin-top: 20px;
            font-size: 12px;
            color: #64748B;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
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

    <div class="icon">
        ✕
    </div>

    <h2>Paiement échoué</h2>

    <p>
        Une erreur est survenue lors du paiement 😔 <br>
        Veuillez réessayer ou utiliser un autre moyen de paiement.
    </p>


    <a href="{{env('FRONTEND_URL')}}" class="btn-secondary">Retour à l'accueil</a>

    <div class="small">
        Référence : {{ request('reference') ?? 'N/A' }}
    </div>

</div>

</body>
</html>
