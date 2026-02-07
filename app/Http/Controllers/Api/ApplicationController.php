<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    
        /**
        * Display a listing of the resource.
        */
        public function index()
        {
            //
        }
    
        /**
        * Store a newly created resource in storage.
        */
        public function store(Request $request)
        {
            
             $validatedData = Validator::make($request->all(), [
                    // 'name' => 'required|unique:applications',
                    'name' => 'required',
                    'paystack_public_key' => 'required',
                    'paystack_private_key' => 'required',
                    'callback_url' => 'required',
                    'webhook_url' => 'required'
                ]);
                // 
                // return "ok it passes ";
                if($validatedData->fails()){
                    // return "ok it fails";
                    return response()->json(['message' => $validatedData->errors()], 422);
                }
        
            //  return $request['name'];
                $new_application = new Application();
                $new_application->name = $request['name'];
                $new_application->paystack_public_key = $request['paystack_public_key'];
                $new_application->paystack_private_key = $request['paystack_private_key'];
                $new_application->callback_url = $request['callback_url'];
                $new_application->webhook_url = $request['webhook_url'];
                $new_application->save();

                return $new_application;
            // return "okokoksssssaas";
       
        }
    
        /**
        * Display the specified resource.
        */
        public function show(string $id)
        {
            //
        }
    
        /**
        * Update the specified resource in storage.
        */
        public function update(Request $request, string $id)
        {
            //
        }
    
        /**
        * Remove the specified resource from storage.
        */
        public function destroy(string $id)
        {
            //
        }
}
