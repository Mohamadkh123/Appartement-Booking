<?php

namespace App\Http\Controllers\API;


use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Exception;

class WalletController extends BaseController
{
    
    public function deposit(Request $request, $userId)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01'
            ]);

            $user = User::findOrFail($userId);

            if (!$user->isTenant()) {
                return $this->sendError('Only tenants can have wallet deposits', [], 400);
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
                $user->wallet()->save($wallet);
            }

            $wallet->balance += $request->amount;
            $wallet->save();

            return $this->sendResponse([
                'user_id' => $user->id,
                'new_balance' => $wallet->balance
            ], 'Deposit successful', 200);
        } catch (Exception $e) {
            return $this->sendError('Deposit failed', ['error' => $e->getMessage()], 500);
        }
    }

    
    public function balance(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            $wallet = $user->wallet;
            if (!$wallet) {
                $wallet = new Wallet(['user_id' => $user->id, 'balance' => 0]);
            }

            return $this->sendResponse([
                'user_id' => $user->id,
                'balance' => $wallet->balance
            ], 'Balance retrieved successfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve balance', ['error' => $e->getMessage()], 500);
        }
    }
}
