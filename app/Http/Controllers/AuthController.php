<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Validator;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    //


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
