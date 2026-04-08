<?php


namespace App\Service\Gemini;

class GeminiPromptBuilder
{
    public static function buildBatchPrompt(array $matches, bool $withAnalysis = false): string
    {
        $matchesList = implode("\n", $matches);


        return <<<PROMPT
Tu es un expert en paris sportifs avec accès à des données statistiques avancées (forme récente, H2H, xG, blessures, etc.). Fournis des prédictions fiables et réalistes pour chaque match en te basant sur des analyses objectives.

INSTRUCTIONS STRICTES :
1. Analyse chaque match individuellement avec des données récentes (5 derniers matchs, H2H, stats offensives/défensives).
2. Réponds **UNIQUEMENT en JSON valide** avec cette structure exacte. Aucun texte hors JSON.
3. Utilise des valeurs numériques entre 0.0 et 100.0 (pourcentages).
4. "match" doit correspondre **exactement** au nom fourni.
5. "best_bets" : liste 1-3 paris recommandés (ex: "Over 2.5", "BTTS Yes", "Home Win").
6. Textes en français, concis (max 2 phrases par champ).

Structure JSON requise :
{
  "predictions": [
    {
      "match": "nom_du_match_exact",
      "score_exact": "X-X",
      "confidence": 75.5,
      "probabilities": {
        "home_win": 65.2,
        "draw": 20.1,
        "away_win": 14.7
      },
      "over_2_5": 68.3,
      "btts_yes": 55.9,
      "best_bets": ["Over 2.5", "BTTS Yes", "Home -0.5"],
      "analysis": "Analyse concise expliquant la prédiction (forme, H2H, xG, etc.).",
      "form_teams": "Forme équipe domicile (5 derniers) | Équipe extérieure (5 derniers)",
      "h2h": "Historique confrontations directes (derniers 5 matchs).",
      "stat_offensive": "Buts marqués/ match (domicile/extérieur).",
      "stat_defensive": "Buts encaissés/ match (domicile/extérieur)."
    }
  ]
}

⚠️ CRITIQUE :
- JSON invalide = échec total.
- Probabilités doivent sommer ≈100% (±5%).
- Prédictions réalistes basées sur stats, PAS aléatoires.
- Confidence >70% seulement si très sûr.

Liste des matchs :
$matchesList
PROMPT;
    }
}
