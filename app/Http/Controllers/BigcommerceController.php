<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use App\Models\StoreInfo;

class BigcommerceController extends Controller
{
    protected $baseURL;

    public function __construct()
    {
        $this->baseURL = config('app.url');
    }

    public function getAppSecret(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_secret');
        } else {
            return config('bigcommerce.bc_app_secret');
        }
    }

    public function getAccessToken(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_access_token');
        } else {
            $store_info = StoreInfo::where('user_id', $request->session()->get('user_id'))->first();
            if ($store_info) {
                return $store_info->access_token;
            }

            return $request->session()->get('access_token');
        }
    }

    public function getStoreHash(Request $request) {
        if (app()->environment('local')) {
            return config('bigcommerce.bc_local_store_hash');
        } else {
            return $request->session()->get('store_hash');
        }
    }

    public function install(Request $request)
    {
        // Make sure all required query params have been passed
        if (!$request->has('code') || !$request->has('scope') || !$request->has('context')) {
            return redirect('error')->with('error_message', 'Not enough information was passed to install this app.');
        }

        try {
            $client = new Client();
            $result = $client->request('POST', 'https://login.bigcommerce.com/oauth2/token', [
                'json' => [
                    'client_id' => $this->getAppClientId(),
                    'client_secret' => $this->getAppSecret($request),
                    'redirect_uri' => $this->baseURL . '/auth/install',
                    'grant_type' => 'authorization_code',
                    'code' => $request->input('code'),
                    'scope' => $request->input('scope'),
                    'context' => $request->input('context'),
                ]
            ]);

            $statusCode = $result->getStatusCode();
            $data = json_decode($result->getBody(), true);

            if ($statusCode == 200) {
                $request->session()->put('store_hash', $data['context']);
                $request->session()->put('access_token', $data['access_token']);
                $request->session()->put('user_id', $data['user']['id']);
                $request->session()->put('user_email', $data['user']['email']);

                $store_info = StoreInfo::where('user_id', $data['user']['id'])->first();

                if ($store_info) {
                    $store_info->update([
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $this->getStoreTimezone($data['context'], $data['access_token'])
                    ]);
                } else {
                    StoreInfo::create([
                        'store_hash' => $data['context'],
                        'access_token' => $data['access_token'],
                        'user_id' => $data['user']['id'],
                        'user_email' => $data['user']['email'],
                        'timezone' => $this->getStoreTimezone($data['context'], $data['access_token'])
                    ]);
                }

                // If the merchant installed the app via an external link, redirect back to the 
                // BC installation success page for this app
                if ($request->has('external_install')) {
                    return redirect('https://login.bigcommerce.com/app/' . $this->getAppClientId() . '/install/succeeded');
                }
            }

            $user_id = $data['user']['id'];
            $storeHash = $data['context'];

            return view('overview.index', compact('storeHash'));
        } catch (\RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = "An error occurred.";

            if ($e->hasResponse()) {
                if ($statusCode != 500) {
                    dd($e->getMessage());
                    // $errorMessage = Psr7\str($e->getResponse());
                    $errorMessage = $e->getMessage();
                }
            }

            // If the merchant installed the app via an external link, redirect back to the 
            // BC installation failure page for this app
            if ($request->has('external_install')) {
                return redirect('https://login.bigcommerce.com/app/' . $this->getAppClientId() . '/install/failed');
            } else {
                return redirect('error')->with('error_message', $errorMessage);
            }
        }
    }

    public function load(Request $request)
    {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                $request->session()->put('user_id', $verifiedSignedRequestData['user']['id']);
                $request->session()->put('user_email', $verifiedSignedRequestData['user']['email']);
                $request->session()->put('owner_id', $verifiedSignedRequestData['owner']['id']);
                $request->session()->put('owner_email', $verifiedSignedRequestData['owner']['email']);
                $request->session()->put('store_hash', $verifiedSignedRequestData['context']);
            } else {
                return redirect('error')->with('error_message', 'The signed request from BigCommerce could not be validated.');
            }
        } else {
            return redirect('error')->with('error_message', 'The signed request from BigCommerce was empty.');
        }

        $store_info = StoreInfo::where('user_id', $verifiedSignedRequestData['user']['id'])->first();

        if ($store_info) {
            $user_id = $verifiedSignedRequestData['user']['id'];
            $storeHash = $verifiedSignedRequestData['context'];

            return view('overview.index', compact('storeHash'));
        } else {
            return redirect('error')->with('error_message', 'Store not found.');
        }
    }

    private function getStoreTimezone($store_hash, $access_token) {
        $client = new Client();
        $result = $client->request('GET', 'https://api.bigcommerce.com/'. $store_hash .'/v2/store', [
            'headers' => [
                'X-Auth-Token'  => $access_token,
                'Content-Type'  => 'application/json',
            ]
        ]);

        $statusCode = $result->getStatusCode();

        if ($statusCode == 200) {
            $data = json_decode($result->getBody(), true);

            if (isset($data['timezone']) && isset($data['timezone']['name'])) {
                return $data['timezone']['name'];
            }
        }

        return '';
    }

    private function verifySignedRequest($signedRequest, $appRequest)
    {
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);

        // confirm the signature
        $expectedSignature = hash_hmac('sha256', $jsonStr, $this->getAppSecret($appRequest), $raw = false);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');
            return null;
        }

        return $data;
    }

    public function makeBigCommerceAPIRequest(Request $request, $endpoint)
    {
        $requestConfig = [
            'headers' => [
                'X-Auth-Client' => $this->getAppClientId(),
                'X-Auth-Token'  => $this->getAccessToken($request),
                'Content-Type'  => 'application/json',
            ]
        ];

        if ($request->method() === 'PUT') {
            $requestConfig['body'] = $request->getContent();
        }

        $client = new Client();
        $result = $client->request($request->method(), 'https://api.bigcommerce.com/' . $this->getStoreHash($request) . '/' . $endpoint, $requestConfig);
        return $result;
    }

    public function proxyBigCommerceAPIRequest(Request $request, $endpoint)
    {
        if (strrpos($endpoint, 'v2') !== false) {
            // For v2 endpoints, add a .json to the end of each endpoint, to normalize against the v3 API standards
            $endpoint .= '.json';
        }

        $result = $this->makeBigCommerceAPIRequest($request, $endpoint);

        return response($result->getBody(), $result->getStatusCode())->header('Content-Type', 'application/json');
    }

    public function uninstall(Request $request) {
        $signedPayload = $request->input('signed_payload');
        if (!empty($signedPayload)) {
            $verifiedSignedRequestData = $this->verifySignedRequest($signedPayload, $request);
            if ($verifiedSignedRequestData !== null) {
                $request->session()->put('user_id', $verifiedSignedRequestData['user']['id']);
                $request->session()->put('user_email', $verifiedSignedRequestData['user']['email']);
                $request->session()->put('owner_id', $verifiedSignedRequestData['owner']['id']);
                $request->session()->put('owner_email', $verifiedSignedRequestData['owner']['email']);
                $request->session()->put('store_hash', $verifiedSignedRequestData['context']);
            } else {
                return redirect('error')->with('error_message', 'The signed request from BigCommerce could not be validated.');
            }
        } else {
            return redirect('error')->with('error_message', 'The signed request from BigCommerce was empty.');
        }
    }
}
