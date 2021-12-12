<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class SignInAppleController extends Controller
{
	public function handleSIWALogin()
	{
		$authorizationCode = request()->input('token'); // 1
		$body = 'client_id=' . config('custom.siwa_client_id') .
			'&client_secret=' . config('custom.siwa_client_secret') .
			'&code=' . $authorizationCode .
			'&grant_type=' . config('custom.siwa_grant_type');
		$client = new Client();
		$request = new Request("POST", "https://appleid.apple.com/auth/token", ["Content-Type" => "application/x-www-form-urlencoded"], $body); // 2
		try {
			$response = $client->send($request); // 3
			$data = json_decode($response->getBody(), true);
			$payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $data['id_token'])[1]))), true); // 4
			if ($payload['email']) return $this->createOrLogUser($payload); // 5
			return $this->respondWithError("Could not authenticate with this token");
		} catch (GuzzleException $e) {
			return $this->respondWithError($e->getMessage()); // 6
		}
	}

	private function createOrLogUser($payload) //: JsonResponse
	{
		Log::info('Create/Log user - ' . print_r($payload, true));
		return redirect()->away("intent://callback?ok#Intent;package=com.sgcomptech.signinapple.sign_in_apple_flutter;scheme=signinwithapple;end");
	}

	private function respondWithError($message) //: JsonResponse
	{
		Log::info("Response with error - $message");
		return redirect()->away("intent://callback?notok#Intent;package=com.sgcomptech.signinapple.sign_in_apple_flutter;scheme=signinwithapple;end");
	}

	public function redirectIntent()
	{
		return redirect()->away("intent://callback?ok#Intent;package=com.sgcomptech.signinapple.sign_in_apple_flutter;scheme=signinwithapple;end");
	}
}
