<?php

namespace App\Http\Controllers;

use App\Models\Pay;
use App\Models\Commandes;
use App\Models\ClientDebt;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayController extends Controller
{
    public function pay(Request $request)
    {
        // Validate the request
        $request->validate([
            'payment_mode' => 'required|string',
            'amount' => 'required|numeric',
            'commandId' => 'required|exists:commandes,id',
            'mobile_number' => 'nullable|string',
            'mobile_reference' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_reference' => 'nullable|string',
            'card_type' => 'nullable|string',
            'card_reference' => 'nullable|string',
            'cash_reference' => 'nullable|string',
            'factures_remboursement' => 'nullable|array',
            'factures_remboursement.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $command = Commandes::find($request->commandId); 


        $clientDebt = ClientDebt::where('client_id', $command->client_id)
        ->where('commande_id' , $request->commandId)
        ->first();

        if( $request->amount > $clientDebt->amount ){
            return back()->with('error' , 'La dette du client est inferieur au montant ');
        }

        $payment = new Pay();
        $payment->user_id = auth()->user()->id;
        $payment->client_id = $command->client_id;

        $payment->amount = $request->amount;
        $payment->payment_method = $request->payment_mode;
        $payment->transaction_id = $this->generateTransactionId(); 
        $payment->commande_id = $request->commandId;

        $clientDebt->amount -= $request->amount;
        
        $client = Clients::where('id', $command->client_id)->first();
        $client->current_debt  = $client->current_debt - $request->amount;
        $client->limit_credit_for_this_user	  = $client->limit_credit_for_this_user	 + $request->amount;

        if ($request->hasFile('factures_remboursement')) {
            $files = $request->file('factures_remboursement');
            foreach ($files as $file) {
                $path = $file->store('factures_remboursements'); 
            }
        }

        $clientDebt->save();
        $client->save();

        $payment->save();

        return redirect()->back()->with('success', 'Remboursement enregistré avec succès!');
    }

    // Generate a unique transaction ID
    private function generateTransactionId()
    {
        return 'TXN-' . strtoupper(uniqid());
    }

    public function show($id)
    {
        $payment = Pay::findOrFail($id);
        return response()->json($payment);
    }

    public function getUserPayments($commandId)
    {
        $payments = Pay::where('command_id', $commandId)->get();
        return response()->json($payments);
    }

    
}

