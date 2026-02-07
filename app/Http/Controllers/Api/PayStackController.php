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
            try{
                    $reference = $request->reference;
                    $transaction = \App\Models\Transaction::where('reference', $reference)->first();
                    $application = Application::find($transaction->app_id);
                    // $testing = 5/0;
                    
                    $data = (new PayStackService())->verifyPayment($reference,$application);
                    $status = $data['data']['status'] ?? null;
                    $URL = $application->callback_url."?reference={$reference}";
                    $URL .= "&status={$status}";
                        
                    return redirect()->away($URL);
            }catch(\Exception $e){
                Log::error("Error in callbackURL: " . $e->getMessage());
                return response()->json(['message' => 'An error occurred in callbackURL: ' . $e->getMessage()], 500);
            }
               
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
