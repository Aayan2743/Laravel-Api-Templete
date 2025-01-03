<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class demoController extends Controller
{
    //

    public function sendMessage(Request $req)
    {
        // Validate the input
        $validatedData = $req->validate([
            'phonenumber' => 'required|string|max:15', // Adjust max length as per your requirements
            'text' => 'required|string|max:500',      // Adjust max length as per your requirements
            'url' => 'nullable|url',                 // Optional but must be a valid URL if provided
        ]);

        // Extract validated data
        $phone = $validatedData['phonenumber'];
        $message = $validatedData['text'];
        $url = $validatedData['url'] ?? null;

        $curl = curl_init();

        $postData = [
            'phonenumber' => $phone,
            'text' => $message,
        ];

        if ($url) {
            $postData['url'] = $url;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.360messenger.net/sendMessage/' . env('360MESSANGER_KEY'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            // Log curl errors
            $error = curl_error($curl);
            curl_close($curl);
            return response()->json(['status' => 'error', 'message' => $error], 500);
        }

        curl_close($curl);

        return response()->json(['status' => 'success', 'response' => json_decode($response, true)]);
    }
}
