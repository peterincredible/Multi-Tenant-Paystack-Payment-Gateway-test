<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Validator;
use App\Services\PayStackService;
use Illuminate\Support\Facades\Log;

class PayStackController extends Controller
{
    //

        public function initializePayment(Request $request)
        { 
            
            $validatedData = Validator::make($request->all(), [
                    'app_id' => 'required|exists:applications,id',
                    'email' => 'required|email',
                    'amount' => 'required|numeric',
                    'currency' => 'required|string',
                    'reference' => 'required|string|unique:transactions,reference',
                ]);
                
                if($validatedData->fails()){
                    // return "ok it fails";
                    return response()->json(['message' => $validatedData->errors()], 422);
                } 
                
               $initialized_url = (new PayStackService())->initializePayment($request);
                // return "ok it passes hhhhhhh ". $initialized_url;
            return response()->json($initialized_url);
        }
        public function callbackURL(Request $request)
        {  
               $reference = $request->reference;
               $transaction = \App\Models\Transaction::where('reference', $reference)->first();
               $application = Application::find($transaction->app_id);
               
               $data = (new PayStackService())->verifyPayment($reference,$application);
               $status = $data['data']['status'] ?? null;
               $URL = $application->callback_url."?reference={$reference}";
               $URL .= "&status={$status}";
                // Log::info("callbackurl -----hmmmmm-----");

            //    $response = (new PayStackService())->verifyPayment($reference, $application->pay

               /*
                $transaction = \App\Models\Transaction::where('reference', $reference)->first();
                if (!$transaction) {
                    return response()->json(['message' => 'Transaction not found'], 404);
                }
                $transaction->status = "success";
                $transaction->paid_at = now();
                $transaction->save();
                */
               return redirect()->away($URL);
        }
        public function webhookURL(Request $request)
        {  
            // Handle webhook events from PayStack here
            // You can verify the event and update transaction status accordingly
            // dd("ok it passes hhhhhhh ");
            (new PayStackService())->handleWebhook($request);
              
            // (new PayStackService())->SendInfoToAppWebhookLister($array);
            // Log::info("Webhook received: hahah thanks localtunnel ".json_encode($array));
            // return response()->json(['message' => 'Webhook received']);
            return response()->json(['message' => 'Webhook received'], 200);
        }

}
