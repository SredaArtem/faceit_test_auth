<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FaceitController extends Controller
{
    private $code_verifier;

    public function index()
    {
        return view('faceit');
    }

    public static function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    public static function generateCodeChallenge($codeVerifier): string
    {
        $codeChallengeMethod = 'sha256';
        return rtrim(strtr(base64_encode(hash($codeChallengeMethod, $codeVerifier, true)), '+/', '-_'), '=');
    }

    public function redirectToProvider()
    {
        $hash = config('faceit.code_verifier');
        $verifier = self::generateCodeVerifier();
        $codeChallange = $this->generateCodeChallenge($verifier);
        session()->put('code_verifier', $verifier);
        Log::info('code_verifier: '.$hash);


        $query = http_build_query([
            'client_id' => config('faceit.client_id'),
            'redirect_uri' => config('faceit.redirect_uri'),
            'response_type' => 'code',
            'scope' => 'openid profile',
            'redirect_popup' => true,
            'debug' => true,
            'code_challenge_method' => 'S256',
            'code_challenge' => $codeChallange
        ]);

        return redirect('https://accounts.faceit.com/?'.$query);
    }

    public function handleProviderCallback(Request $request)
    {
        Log::info('Callback request: '.json_encode($request->all()));

        //$http = new Client();

        $client_id = config('faceit.client_id');
        $client_secret = config('faceit.client_secret');
        $redirect_uri = config('faceit.redirect_uri');
        $code_challenge = '47DEQpj8HBSa-_TImW-5JCeuQeRkm5NMpJWZG3hSuFU';

        $credentials = base64_encode($client_id.':'.$client_secret);
        //Log::debug('Faceit credentials: ' . $credentials);

        $client = new Client();

        /*$response = $client->post("https://api.faceit.com/auth/v1/oauth/token", [
            'form_params' => [
                'code'       => $request->input('code'),
                'grant_type' => 'authorization_code',
            ],
            'headers'     => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.base64_encode($client_id.':'.$client_secret),
            ],
        ]);

        dd($response);*/

        // URL для запроса
        /*$url = "https://api.faceit.com/auth/v1/oauth/token";

        $post_data = [
            'code' => $request->input('code'),
            'grant_type' => 'authorization_code',
            'code_verifier' => hash('sha256', config('faceit.code_verifier'))
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

        $response = curl_exec($ch);

        dd($response);*/

        $code = $request->get('code');
        $code_verifier = session()->get('code_verifier');
        Log::info('code_verifier2: '.$code_verifier);

        //dd(strlen(config('faceit.code_verifier')));
        $data = [
            'headers' => [
                'Authorization' => 'Basic '.$credentials,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'authorization_code',
                //'client_id' => $client_id,
                //'client_secret' => $client_secret,
                'code' => $request->get('code'),
                //'redirect_uri' => $redirect_uri,
                //'code_verifier' => hash('sha256', config('faceit.code_verifier')),
                //'code_verifier' => config('faceit.code_verifier'),
                'code_verifier' => $code_verifier,
//                'code_verifier' => hash('sha256', config('faceit.code_verifier')),
                //'debug' => true
            ]
        ];

        //dd($data);

        //try {
        $response = $client->post("https://api.faceit.com/auth/v1/oauth/token", $data);

        $responseBody = json_decode($response->getBody(), true);

        //return response()->json($responseBody);
        /*} catch (Exception $e) {
            // Log the error for debugging
            Log::error('Faceit API error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $responseBody = (string) $response->getBody();
                Log::error('Faceit API response: ' . $responseBody);

                //return response()->json(['error' => $responseBody], $response->getStatusCode());
            }

            //return response()->json(['error' => 'An error occurred'], 500);
        }*/
        $tokens = json_decode((string) $response->getBody(), true);

        dd($tokens);
        Log::info('Tokens: '.json_encode($response));
        die();

        $userResponse = $http->get('https://api.faceit.com/auth/v1/resources/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer '.$tokens['access_token'],
            ],
        ]);

        Log::info('User: '.json_encode(json_decode((string)$userResponse->getBody(), true)));

        /*
        $response = $this->getHttpClient()->get('https://api.faceit.com/core/v1/users/me', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
        */

        return redirect('/');
        die();

        $faceitUser = json_decode((string)$userResponse->getBody(), true);

        $user = User::updateOrCreate(
            ['faceit_id' => $faceitUser['guid']],
            [
                'id' => $faceitUser['id'],
                'nickname' => $faceitUser['username'],
                'name' => $faceitUser['full_name'],
                'email' => $faceitUser['email'],
                'avatar' => $faceitUser['avatar'],
                // etc
            ]
        );

        Auth::login($user);

        return redirect('/');
    }
}
