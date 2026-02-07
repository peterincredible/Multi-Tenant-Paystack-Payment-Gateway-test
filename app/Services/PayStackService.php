<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use stdClass;

class PayStackService
{
    const PENDING="pending";
    const SUCCESSFUL="successful";
    const FAILED="failed";

    private string $base_url = 'https://api.paystack.co';
    public function initializePayment($request)
    {
        // return "okkkkkk";
        /* */
        try{
            $application = Application::find($request->app_id);
        
        
            $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $application->paystack_private_key,
                            'Content-Type' => 'application/json', ])
                ->post("{$this->base_url}/transaction/initialize", [
                    'email' => $request->email,
                    'amount' => (string)($request->amount * 100),
                    'currency' => $request->currency,
                    'reference' => $request->reference,
                    'callback_url' => route('callback_url'),
                ]);
                
                $transaction_data = new Transaction();
                $transaction_data->app_id = $request->app_id;
                $transaction_data->email = $request->email;
                $transaction_data->reference = $request->reference;
                $transaction_data->amount = $request->amount;
                $transaction_data->currency = $request->currency;
                $transaction_data->status = self::PENDING;
                $transaction_data->save();

                // return $application->paystack_private_key."  ". $application->callback_url;
                return $response->json();
        }catch(\Exception $e){
            Log::error("Error initializing payment: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while initializing payment: ' . $e->getMessage()], 500);
        }
        
    }

    public function verifyPayment($reference,$application)
    {

            try{
                $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $application->paystack_private_key,
                            'Content-Type' => 'application/json'
                            ])
                            ->get("{$this->base_url}/transaction/verify/{$reference}"); 
                $data = $response->json();
                $status = self::PENDING;
                if($data['status'] && $data['data']['status'] === 'success'){
                    $status = self::SUCCESSFUL;
                }else{
                    $status = self::FAILED;
                }
                $temp_data = new stdClass();
                $temp_data->status = $status;
                $temp_data->paid_at = date('Y-m-d H:i:s', strtotime($data['data']['paid_at'] ?? now()));//$data['data']['paid_at'] ?? null;
                $temp_data->gateway_response = $data['data']['gateway_response'] ?? null;
                $temp_data->channel = $data['data']['channel'] ?? null;
                $temp_data->raw_payload = json_encode($data);
                $this->updateTransaction($reference, $temp_data);
              /*
                if($data['status'] && $data['data']['status'] === 'success'){
                    
                    $transaction = \App\Models\Transaction::where('reference', $reference)->first();
                    if (!$transaction) {
                        return response()->json(['message' => 'Transaction not found'], 404);
                    }
                    $transaction->status = self::SUCCESSFUL;
                    $transaction->paid_at = now();
                    $transaction->gateway_response = $data['data']['gateway_response'] ?? null;
                    $transaction->channel = $data['data']['channel'] ?? null;
                    $transaction->raw_payload = json_encode($data);
                    $transaction->save();

                    

                }else{
                    
                        $transaction = Transaction::where('reference', $reference)->first();
                        if (!$transaction) {
                            return response()->json(['message' => 'Transaction not found'], 404);
                        }
                        $transaction->status = self::FAILED;
                        $transaction->gateway_response = $data['data']['gateway_response'] ?? null;
                        $transaction->channel = $data['data']['channel'] ?? null;
                        $transaction->raw_payload = json_encode($data);
                        $transaction->save();
                    
                }

                */
            return $data;

        }catch(\Exception $e){
            Log::error("Error verifying payment: " . $e->getMessage());
            return response()->json(['message' => 'An error occurred while verifying payment: ' . $e->getMessage()], 500);
        }
        
        
    }
    public function handleWebhook($request)
    {
           $reference = $request->data['reference'];
           $transaction = Transaction::where('reference', $reference)->first();
           $application = Application::find($transaction->app_id);
           $payload = $request->getContent();
           $paystackSignature = $request->header('x-paystack-signature');
           $secretKey = $application->paystack_private_key;
           $localSignature = hash_hmac('sha512', $payload, $secretKey);
           $status = self::PENDING;
           if($request->event == "charge.success"){
               $status = self::SUCCESSFUL;
              }
            elseif($request->event == "charge.failed"){
                $status = self::FAILED;
            }
           if ($localSignature === $paystackSignature) {
                // Log::info('Valid Paystack webhook received. Event type: ' . $request->event);
                // return response()->json(['status' => 'success'], 200);
                $data = new stdClass();
                $data->status = $status;
                $data->paid_at = date('Y-m-d H:i:s', strtotime($request->data['paid_at'] ?? now()));//now();//$request->data['paid_at'] ?? null;
                $data->gateway_response = $request->data['gateway_response'] ?? null;
                $data->channel = $request->data['channel'] ?? null; 
                $data->raw_payload = json_encode($request->all());
                $this->updateTransaction($reference, $data);
                $array=[
                "event"=>$request->event,
                "data"=>[
                        "id"=>$request->data['id'] ?? null,
                        "status"=>$request->data['status'] ?? null,
                        "reference"=>$request->data['reference'] ?? null,
                        "amount"=>$request->data['amount'] ?? null,
                        "currency"=>$request->data['currency'] ?? null,
                    ]
                ];
                dispatch(function() use ($array,$application){
                    (new PayStackService())->SendInfoToAppWebhookLister($array,$application);
                });  

                
            } else {
                  Log::warning('Invalid Paystack webhook signature. Request rejected.');
                //  return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }

    }

    public function SendInfoToAppWebhookLister($array,$application){
        try{
            Log::info("sending...... to app webhook listener url.. ".json_encode($array));
            $response = Http::withHeaders([
                            'Content-Type' => 'application/json'])
                ->post($application->webhook_url, $array);
            

        }catch(\Exception $e){
            Log::error("Error sending info to app webhook listener: " . $e->getMessage());
            
        }
        
    }

    public function updateTransaction($reference,$data){
        $transaction = Transaction::where('reference', $reference)->first();
        if($transaction && $transaction->status == self::PENDING){
            $transaction->status = $data->status;
            $transaction->paid_at = $data->paid_at ?? now();
            $transaction->gateway_response = $data->gateway_response?? null;
            $transaction->channel = $data->channel ?? null;
            $transaction->raw_payload = $data->raw_payload ?? null;
            $transaction->save();
        }
        
    }
}