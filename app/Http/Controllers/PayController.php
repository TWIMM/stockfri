<?php

namespace App\Http\Controllers;

use App\Models\Pay;
use App\Models\Command;
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

        // Create a new payment record
        $payment = new Pay();
        $payment->user_id = auth()->user()->id;
        $payment->amount = $request->amount;
        $payment->payment_method = $request->payment_mode;
        $payment->transaction_id = $this->generateTransactionId(); // Generate a transaction ID, you can customize this method
        $payment->commande_id = $request->commandId; // Assuming the command ID is passed in the request

        

        // Handle the uploaded invoices (factures)
        if ($request->hasFile('factures_remboursement')) {
            $files = $request->file('factures_remboursement');
            foreach ($files as $file) {
                $path = $file->store('factures_remboursements'); // Store the file in a directory called 'factures'
                // Here, you can save the file path in the database or associate it with the payment record
            }
        }

        // Save the payment record
        $payment->save();

        // Return a success response or redirect
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

