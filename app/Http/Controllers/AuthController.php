<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\otp;
use Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;


use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    //

    protected function existingOtp($phone,$otp){
      
      
        $url = 'https://api.360messenger.com/sendMessage/PAi5DW7jsM7E0qoE6zzyeKP4ClK7foKJtOr';
        $data = [
            'phonenumber' => $phone,
            'text' => $Otp,
       
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return response()->json(['error' => 'Failed to send message', 'details' => curl_error($ch)], 500);
    }
    curl_close($ch);
       

        return $response;
       


    }



    protected function generateOtp($phone,$key){
        $Otp = rand(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(2);
        $url = 'https://api.360messenger.com/sendMessage/PAi5DW7jsM7E0qoE6zzyeKP4ClK7foKJtOr';
        $data = [
            'phonenumber' => $phone,
            'text' => $Otp,
       
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return response()->json(['error' => 'Failed to send message', 'details' => curl_error($ch)], 500);
    }
    curl_close($ch);
        $saveData=otp::create([
            'mobile'=>$phone,
            'OTP'=>$Otp,
            'validateTime'=>$expiresAt,
            'api_key'=>$key,
            'unique_id'=>$response,
            'status'=>true,
        ]);

        return $response;
       


    }


    public function sendMessageWithCurl(Request $req)
{

    $validator=Validator::make($req->all(),[
        'phone'=>'required|string|min:2|max:100',
        // 'text'=>'required|string|max:500',
        'url'=>'nullable|url'
    ]);

    if($validator->fails()){
        return response()->json([
            'errors'=>
            $validator->errors()
        ],422);
    }
    $Otp = rand(1000, 9999);
    $postData = [
            'phonenumber' => $req->phone,
            'text' => $Otp,
        ];

    if (!empty($req->url)) {
        $postData['url'] = $req->url;
    } 

    $key="PAi5DW7jsM7E0qoE6zzyeKP4ClK7foKJtOr";


    $checkalready_exist=otp::where('mobile',$req->phone)->where('status',1)->
    where('api_key',$key)->get();

    if ($checkalready_exist->isNotEmpty() || Carbon::now()->greaterThan(Carbon::parse($checkalready_exist[0]->validateTime))) {
        // OTP exists but has expired; generate a new one
        $res = $this->generateOtp($req->phone, $key);
        dd("Expired OTP, generated new", $res);
    } elseif ($checkalready_exist->isNotEmpty()) {
        // OTP exists and is valid
        $res = $this->existingOtp($req->phone, $checkalready_exist[0]->OTP);
        
        dd("Existing OTP is valid", $checkalready_exist[0]->OTP);
    } else {
        // No OTP exists; generate a new one
        $res = $this->generateOtp($req->phone, $key);
        dd("Generated new OTP", $res);
    }

    
   
    
    dd("stop");
    


    

    
}

public function verifyOtp(Request $request)
{
    $validator=Validator::make($request->all(),[
        'phone_number' => 'required',
        'otp' => 'required|digits:4',
    ]);

    if($validator->fails()){
        return response()->json([
            'errors'=>
            $validator->errors()
        ],422);
    }

    $record = otp::where('mobile', $request->phone_number)
        ->where('OTP', $request->otp)
        ->where('status', 1)
        ->first();

    if (!$record) {
        return response()->json(['error' => 'Invalid OTP.'], 400);
    }

    if (Carbon::now()->greaterThan(Carbon::parse($record->validateTime))) {
        return response()->json(['error' => 'OTP expired.'], 400);
    }

    // OTP is valid
    return response()->json(['message' => 'OTP verified successfully!']);
    

    
}

    


public function sendMessage(Request $req)
{
    // dd("test");
    $validator=Validator::make($req->all(),[
        'phone'=>'required|string|min:2|max:100',
        'text'=>'required|string|max:500',
        'url'=>'nullable|url'
    ]);

    if($validator->fails()){
        return response()->json([
            'errors'=>
            $validator->errors()
        ],422);
    }

    $postData = [
            'phonenumber' => $req->phone,
            'text' => $req->text,
        ];

    if (!empty($req->url)) {
        $postData['url'] = $req->url;
    }    

      
    $response = Http::post('https://api.360messenger.net/sendMessage/' . env('360MESSANGER_KEY'), $postData);

    // Check if the response is successful
    if ($response->successful()) {
        return response()->json(['status' => 'success', 'response' => $response->json()]);
    }

    // Handle error responses
    return response()->json([
        'status' => 'error',
        'message' => $response->body(), // Detailed error message
    ], $response->status());
}





    public function get_data($id){

          $details=User::findOrFail($id);
          
          return response()->json([
            'status'=>true,
            'details'=>$details
          ]);

    }

    public function update_data(Request $req){
        $validator=Validator::make($req->all(),[
            'id'=>'required|exists:users,id',
            'name'=>'required|string',
            'email' => 'required|email|unique:users,email,' . $req->id,
        ]);
    
        if($validator->fails()){
            return response()->json([
                'errors'=>
                $validator->errors()
            ],422);
        }

        try {
            $user = User::findOrFail($req->id);
            $user->update([
                'name' => $req->name,
                'email' => $req->email,
            ]);
            
    
            return response()->json([
                'status'=>true,
                'message' => 'User details updated successfully!',
                'details' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user details!',
                'error' => $e->getMessage(),
            ], 500);
        }

        
    }



    public function register(Request $req){

        $validator=Validator::make($req->all(),[
            'name'=>'required|string|min:2|max:100',
            'email'=>'required|string|email|max:100|unique:users',
            'password'=>'required|min:6'
        ]);
    
        if($validator->fails()){
            return response()->json([
                'errors'=>
                $validator->errors()
            ],422);
        }
    
        $userresult=User::create([
            'name'=>$req->name,
            'email'=>$req->email,
            'password'=>Hash::make($req->password)
        ]);
    
        return response()->json([
            'message' => 'user registered successfully',
            'user'=>$userresult
            
        ]);
    
    
    }

    public function login(Request $req){

        $validator=Validator::make($req->all(),[
            'email'=>'required|email|string',
            'password'=>'required'
        ]);
    
        if($validator->fails()){
            return response()->json([
                'errors'=>
                $validator->errors()
            ],422);
        }
    
        if(!$token=auth()->attempt($validator->validated())){
    
            return response()->json([
                'error'=>'Unauthorized'
            ]);
        }
    
        return $this->resondedJwtToken($token);
    
    
    }
    

    protected function resondedJwtToken($token){
    
        return response()->json([
        
        'access_token'=>$token,
        'token_type'=>'bearer',
        // 'expires_in'=> auth()->factory()->getTTL()*60
        'expires_in'=> auth()->factory()->getTTL()*60
        
        ]);
    
    }


    public function list(){
        return response()->json([
            'status'=>true,
            'data'=>User::get()
        ]);
    }
    
    
}
