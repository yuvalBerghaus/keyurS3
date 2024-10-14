<?php
use Aws\Credentials\CredentialProvider;
use Illuminate\Support\Facades\Route;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/invoke-aws-api', function () {
    // AWS API Gateway details
    $host = 'jfrnmjmm48.execute-api.us-east-2.amazonaws.com';
    $path = '/prod/getSignedUrl';
    $region = 'us-east-2';
    $service = 'execute-api';
    $method = 'POST';
    $body = json_encode(['filenames' => ['keyur.jpg']]);

    try {
        // Step 1: Get credentials
        $provider = CredentialProvider::defaultProvider();
        $credentials = $provider()->wait();

        // Step 2: Create the AWS Signature V4 signer
        $signer = new SignatureV4($service, $region);
        $request = new Request($method, "https://{$host}{$path}", [
            'Content-Type' => 'application/json',
        ], $body);

        // Step 3: Sign the request
        $signedRequest = $signer->signRequest($request, $credentials);

        // Step 4: Send the signed request
        $client = new Client();
        $response = $client->send($signedRequest);

        // Return the response from the API Gateway
        return response()->json(json_decode($response->getBody(), true));
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error invoking AWS API: ' . $e->getMessage()], 500);
    }
});