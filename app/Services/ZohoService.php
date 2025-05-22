<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZohoService
{
    private string $baseUrl;
    private string $accountsUrl;
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;

    public function __construct()
    {
        $this->baseUrl = config('zoho.api').'/crm/v8';
        $this->accountsUrl = config('zoho.accounts_url');
        $this->clientId = config('zoho.client_id');
        $this->clientSecret = config('zoho.client_secret');
        $this->refreshToken = config('zoho.refresh_token');
    }

   public function createAccount($data)
   {
       $body = [
           'data' => [
               [
                   'Account_Name' => $data->account_name,
                   'Website' => $data->website,
                   'Phone' => $data->phone,
               ]
           ]
       ];

       return $this->postZoho('Accounts', $body);
   }

   public function createDeal($data, string $accountId)
   {
       $body = [
           'data' => [
               [
                   'Deal_Name' => $data->deal_name,
                   'Stage' => $data->stage,
                   'Account_Name' => [
                       'id' => $accountId,
                   ],
               ]
           ]
       ];

       return $this->postZoho('Deals', $body);
   }

   public function getDeal(string $dealId)
   {
       return $this->getZoho('Deals', $dealId);
   }

    private function getZoho(string $endpoint, string $dealId)
    {
        $path = "{$this->baseUrl}/{$endpoint}/{$dealId}";

        return $this->handleResponse(Http::withToken($this->token())
            ->get($path)->json());
    }

    private function postZoho(string $endpoint, array $body)
    {
        $path = "{$this->baseUrl}/{$endpoint}";

        return $this->handleResponse(Http::withToken($this->token())
            ->post($path, $body)->json());
    }

    private function token()
    {
        return Cache::get('zoho.access_token')
            ?? $this->refreshAccessToken();
    }

    private function refreshAccessToken(): string
    {
        $response = Http::asForm()->post(
            "{$this->accountsUrl}/oauth/v2/token",
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]
        )->throw()->json();

        Cache::put(
            'zoho.access_token',
            $response['access_token'],
            now()->addSeconds($response['expires_in'] - 300)
        );

        return $response['access_token'];
    }

    private function handleResponse($response)
    {
        if (isset($response['data'][0])) {
            $response = $response['data'][0];
        }

        if (!isset($response['code']) || $response['code'] === 'SUCCESS') {
            return $response;
        }

        $code = $response['code'];
        $details = $response['details'];

        switch ($code) {
            case 'INVALID_MODULE':
            case 'INVALID_MODULEHTTP':
                $hint = 'Перевірте правильність модуля або права доступу.';
                break;

            case 'INVALID_DATA':
                $field = $details['api_name'] ?? '';
                $hint  = "Поле \"$field\" отримало невірний формат.";
                break;

            case 'MULTIPLE_OR_MULTI_ERRORS':
            case 'DUPLICATE_DATA':
                $hint = 'Є дублікати в полях.';
                break;

            case 'OAUTH_SCOPE_MISMATCH':
                $hint = 'Немає потрібних OAuth прав. Перегенеруйте токен.';
                break;

            case 'NO_PERMISSION':
                $hint = 'Немає прав на створення записів.';
                break;

            case 'LIMIT_EXCEEDED':
                $hint = 'Перевищено ліміт записів за один запит.';
                break;

            case 'RECORD_LOCKED':
                $hint = 'Запис заблоковано, операція заборонена.';
                break;

            default:
                $hint = 'Зверніться до адміністратора.';
        }

        if (isset($response['message'])) {
            $response['message'] = $response['message'] . ' - ' . $hint;
        } else {
            $response['message'] = $hint;
        }

        return $response;
    }
}
