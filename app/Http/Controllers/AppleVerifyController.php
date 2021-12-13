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
		Log::info("All request inputs - " . print_r($request->all(), true));
		/* $request->all()
		(
			[code] => cef235c20a09d4aa68087ccef4133ae33.0.rzyv.vrj1q6FT8YFy9N0T17bb-Q
			[id_token] => eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiY29tLnNnY29tcHRlY2guYmVibHV1aTIiLCJleHAiOjE2Mzk0NDMzNzAsImlhdCI6MTYzOTM1Njk3MCwic3ViIjoiMDAwOTg1LjNjOWFlYjRkNGZhOTQ2M2M5Y2U0ZmQ4Y2UxNzE0NDYzLjE2NTkiLCJjX2hhc2giOiI0U2IxcF9XLS16VFZiOWpvd2Q4N0NRIiwiZW1haWwiOiJsYXdyZW5jZTI5NkBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6InRydWUiLCJhdXRoX3RpbWUiOjE2MzkzNTY5NzAsIm5vbmNlX3N1cHBvcnRlZCI6dHJ1ZX0.mbMfdcyFcu8haZxF7STevMu1HhyaRgtIyRTrcZcq8e5JPQ9nN2d5EhVKYXJ9JELxwL_egQZ7j-tXPNv_F5Kzc2oj63jgY8mszJ8Bjb903Cu9WP-kdiSmjJC4uJXm6sQSwnXjO_ZynsoMdxaRQAgGjI-RHmyy544XSuniQ2hKBKm24b4ckcKMk9t97ML37WovuLyhjbqZGYznXXh_9gjRQER8iOO3oby93W4frcX59LD-haIMHNhSQaCuhg0-We7R2plU7APcFkeOZZcZoPqEfkPup_OWksYP46Qh1Sh8AuHnyCtJoBbZjQq4ief81eLKcdiApq8bAL62ID5BbcK2ew
			[user] => {"name":{"firstName":"Kai Mun","lastName":"Lee"},"email":"lawrence296@gmail.com"}
		)
		*/

		$id_token = $request->id_token;
		$client_authorization_code = $request->code;
		$teamId = config('custom.siwa_team_id');
		$clientId = config('custom.siwa_client_id');
		$keyID = config('custom.siwa_key_id');
		$privKey = config('custom.siwa_priv_key');

		$jwks = json_decode(file_get_contents("https://appleid.apple.com/auth/keys"), true);
		/*
		$apple_jwk_keys = json_decode(file_get_contents("https://appleid.apple.com/auth/keys"), null, 512, JSON_OBJECT_AS_ARRAY);
		$apple_jwk_keys after json_decode :
		[
     "keys" => [
       [
         "kty" => "RSA",
         "kid" => "eXaunmL",
         "use" => "sig",
         "alg" => "RS256",
         "n" => "4dGQ7bQK8LgILOdLsYzfZjkEAoQeVC_aqyc8GC6RX7dq_KvRAQAWPvkam8VQv4GK5T4ogklEKEvj5ISBamdDNq1n52TpxQwI2EqxSk7I9fKPKhRt4F8-2yETlYvye-2s6NeWJim0KBtOVrk0gWvEDgd6WOqJl_yt5WBISvILNyVg1qAAM8JeX6dRPosahRVDjA52G2X-Tip84wqwyRpUlq2ybzcLh3zyhCitBOebiRWDQfG26EH9lTlJhll-p_Dg8vAXxJLIJ4SNLcqgFeZe4OfHLgdzMvxXZJnPp_VgmkcpUdRotazKZumj6dBPcXI_XID4Z4Z3OM1KrZPJNdUhxw",
         "e" => "AQAB",
       ],
       [
         "kty" => "RSA",
         "kid" => "86D88Kf",
         "use" => "sig",
         "alg" => "RS256",
         "n" => "iGaLqP6y-SJCCBq5Hv6pGDbG_SQ11MNjH7rWHcCFYz4hGwHC4lcSurTlV8u3avoVNM8jXevG1Iu1SY11qInqUvjJur--hghr1b56OPJu6H1iKulSxGjEIyDP6c5BdE1uwprYyr4IO9th8fOwCPygjLFrh44XEGbDIFeImwvBAGOhmMB2AD1n1KviyNsH0bEB7phQtiLk-ILjv1bORSRl8AK677-1T8isGfHKXGZ_ZGtStDe7Lu0Ihp8zoUt59kx2o9uWpROkzF56ypresiIl4WprClRCjz8x6cPZXU2qNWhu71TQvUFwvIvbkE1oYaJMb0jcOTmBRZA2QuYw-zHLwQ",
         "e" => "AQAB",
       ],
       [
         "kty" => "RSA",
         "kid" => "YuyXoY",
         "use" => "sig",
         "alg" => "RS256",
         "n" => "1JiU4l3YCeT4o0gVmxGTEK1IXR-Ghdg5Bzka12tzmtdCxU00ChH66aV-4HRBjF1t95IsaeHeDFRgmF0lJbTDTqa6_VZo2hc0zTiUAsGLacN6slePvDcR1IMucQGtPP5tGhIbU-HKabsKOFdD4VQ5PCXifjpN9R-1qOR571BxCAl4u1kUUIePAAJcBcqGRFSI_I1j_jbN3gflK_8ZNmgnPrXA0kZXzj1I7ZHgekGbZoxmDrzYm2zmja1MsE5A_JX7itBYnlR41LOtvLRCNtw7K3EFlbfB6hkPL-Swk5XNGbWZdTROmaTNzJhV-lWT0gGm6V1qWAK2qOZoIDa_3Ud0Gw",
         "e" => "AQAB",
       ],
     ],
   ]
		$keys = array();
		foreach ($apple_jwk_keys->keys as $key)
			$keys[] = (array)$key;
		$jwks = ['keys' => $keys];
		*/

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
