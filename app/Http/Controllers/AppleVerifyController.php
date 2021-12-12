<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

class AppleVerifyController extends Controller
{
	public function appleVerify(Request $request)
	{
		/* Expected
  firstName: credentials.givenName,
  lastName: credentials.familyName,
  authorizationCode: credentials.authorizationCode,
	idToken: credentials.identityToken,
  isAndroid: Platform.isAndroid,

	OR
	code=c4f89bed93fa0476982c1d17549ac3231.0.rrzrr.q3kBNYdzaM6lO84iXNmEfQ&
	id_token=eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ....
*/
		Log::info("All request inputs - " . print_r($request->all(), true));

		$id_token = $request->identityToken;
		$client_authorization_code = $request->authorizationCode;
		$teamId = config('custom.siwa_team_id');
		$clientId = config('custom.siwa_client_id');
		$keyID = config('custom.siwa_key_id');
		$privKey = config('custom.siwa_priv_key');

		$apple_jwk_keys = json_decode(file_get_contents("https://appleid.apple.com/auth/keys"), null, 512, JSON_OBJECT_AS_ARRAY);
		$keys = array();
		foreach ($apple_jwk_keys->keys as $key)
			$keys[] = (array)$key;
		$jwks = ['keys' => $keys];

		$header_base_64 = explode('.', $id_token)[0];
		$kid = JWT::jsonDecode(JWT::urlsafeB64Decode($header_base_64));
		$kid = $kid->kid;

		$public_key = JWK::parseKeySet($jwks);
		$public_key = $public_key[$kid];

		$payload = array(
			"iss" => $teamId,
			'aud' => 'https://appleid.apple.com',
			'iat' => time(),
			'exp' => time() + 3600,
			'sub' => $clientId
		);

		$client_secret = JWT::encode($payload, $privKey, 'ES256', $keyID);

		$post_data = [
			'client_id' => $clientId,
			'grant_type' => 'authorization_code',
			'code' => $client_authorization_code,
			'client_secret' => $client_secret
		];
		Log::info("POST data : " . print_r($post_data, true));

		$ch = curl_init("https://appleid.apple.com/auth/token");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Accept: application/x-www-form-urlencoded',
			'User-Agent: curl',  //Apple requires a user agent header at the token endpoint
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
		$curl_response = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($curl_response, true);
		Log::info("Response data : " . print_r($data, true));
		$refresh_token = $data['refresh_token'];

		$claims = explode('.', $data['id_token'])[1];
		$claims = json_decode(base64_decode($claims));
		Log::info("Claims : " . print_r($claims, true));

		return redirect()->away("intent://callback?ok#Intent;package=" .
		  config('custom.app_package') . ";scheme=signinwithapple;end");
	}
}
