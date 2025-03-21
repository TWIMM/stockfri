<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Clients extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name', 
        'email', 
        'tel', 
        'trusted', 
        'limit_credit_for_this_user',
        'address',
        'credit_score',         // Score de crédit actuel du client
        'credit_limit',         // Limite maximum de crédit autorisée
        'current_debt',         // Dette actuelle
        'last_score_update',    // Date de dernière mise à jour du score
    ];

    protected $casts = [
        'credit_score' => 'integer',
        'credit_limit' => 'decimal:2',
        'current_debt' => 'decimal:2',
        'last_score_update' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Commandes::class);
    }

    public function payments()
    {
        return $this->hasMany(Paiement::class);
    }

    public function creditHistory()
    {
        return $this->hasMany(CreditHistory::class);
    }

    /**
     * Calcule le score de crédit du client
     * 
     * @return int
     */
    public function calculateCreditScore()
    {
        // Facteurs de pondération
        $punctualityWeight = 0.4;
        $repaymentWeight = 0.3;
        $historyWeight = 0.15;
        $transactionWeight = 0.15;

        // 1. Calcul de la ponctualité des paiements
        $allPayments = $this->payments()->where('created_at', '>=', Carbon::now()->subMonths(6))->get();
        $latePayments = $this->payments()->where('is_late', true)
                               ->where('created_at', '>=', Carbon::now()->subMonths(6))->count();
        
        $totalPayments = $allPayments->count();
        $punctualityScore = $totalPayments > 0 ? 
            100 * (($totalPayments - $latePayments) / $totalPayments) : 50;

        // 2. Calcul du pourcentage de remboursement
        $totalDebt = $this->purchases()->where('rest_to_pay' , '>', 0.00)->sum('total');
        $totalPaid = $this->payments()->where('created_at', '>=', Carbon::now()->subMonths(6))->sum('amount');
        $repaymentScore = ($totalDebt + $totalPaid) > 0 ? 
            min(100, 100 * ($totalPaid / ($totalDebt + $totalPaid))) : 50;

        // 3. Ancienneté du client
        $clientAge = Carbon::parse($this->created_at)->diffInMonths(Carbon::now());
        $historyScore = min(100, $clientAge * 5); // 5 points par mois jusqu'à 100

        // 4. Volume et fréquence des transactions
        $purchaseCount = $this->purchases()->where('created_at', '>=', Carbon::now()->subMonths(6))->count();
        $transactionScore = min(100, $purchaseCount * 10); // 10 points par achat jusqu'à 100

        // Calcul du score final
        $finalScore = ($punctualityScore * $punctualityWeight) +
                      ($repaymentScore * $repaymentWeight) +
                      ($historyScore * $historyWeight) +
                      ($transactionScore * $transactionWeight);

        // Arrondir le score
        $finalScore = round($finalScore);
        
        // Sauvegarder l'historique du score
        $this->creditHistory()->create([
            'score' => $finalScore,
            'punctuality_score' => $punctualityScore,
            'repayment_score' => $repaymentScore,
            'history_score' => $historyScore,
            'transaction_score' => $transactionScore,
        ]);

        // Mettre à jour le score du client
        $this->credit_score = $finalScore;
        $this->last_score_update = Carbon::now();
        $this->updateCreditLimit(); // Met à jour la limite de crédit en fonction du score
        $this->save();

        return $finalScore;
    }

    /**
     * Met à jour la limite de crédit en fonction du score
     */
    public function updateCreditLimit()
    {
        $baseLimit = $this->user->limite_credit;  //limit de credit a updatepar user dans les settings
        
        if ($this->credit_score >= 90) {
            $this->credit_limit = $baseLimit;
        } elseif ($this->credit_score >= 70) {
            $this->credit_limit = $baseLimit * 0.75;
        } elseif ($this->credit_score >= 50) {
            $this->credit_limit = $baseLimit * 0.5;
        } elseif ($this->credit_score >= 30) {
            $this->credit_limit = $baseLimit * 0.25;
        } else {
            $this->credit_limit = 0;
        }
    }

    /**
     * Vérifie si le client peut acheter à crédit
     * 
     * @param float $amount Montant de l'achat
     * @return bool
     */
    public function canPurchaseOnCredit($amount)
    {
        // Vérifier si le score est à jour
        if ($this->last_score_update === null || 
            Carbon::parse($this->last_score_update)->diffInDays(Carbon::now()) > 30) {
            $this->calculateCreditScore();
        }

        // Vérifier si le montant demandé dépasse la limite disponible
        $availableCredit = $this->credit_limit - $this->current_debt;
        return $availableCredit >= $amount;
    }

    /**
     * Ajoute une dette au client
     * 
     * @param float $amount
     * @return bool
     */
    public function addDebt($amount)
    {
        if ($this->canPurchaseOnCredit($amount)) {
            $this->current_debt += $amount;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Réduit la dette du client après un paiement
     * 
     * @param float $amount
     * @return void
     */
    public function reduceDebt($amount)
    {
        $this->current_debt = max(0, $this->current_debt - $amount);
        $this->save();
        
        // Recalculer le score après un paiement significatif
        if ($amount > 1000) {
            $this->calculateCreditScore();
        }
    }

    /**
     * Retourne le niveau de risque du client
     * 
     * @return string
     */
    public function getRiskLevel()
    {
        if ($this->credit_score >= 90) return 'Très faible';
        if ($this->credit_score >= 70) return 'Faible';
        if ($this->credit_score >= 50) return 'Moyen';
        if ($this->credit_score >= 30) return 'Élevé';
        return 'Très élevé';
    }
}