<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Services\ZohoService;

class ZohoController extends Controller
{
    public function __construct(private ZohoService $zohoService)
    {
    }

    public function store(AccountRequest $request)
    {
        $resultAccount = $this->zohoService->createAccount($request);
        $code = $resultAccount['code'] ?? '';
        $accountId = $resultAccount['details']['id'] ?? null;

        if ($code !== 'SUCCESS' || !$accountId) {
            return response()->json(['data' => [
                'message' => $resultAccount['message'],
                'status' => $resultAccount['status'],
            ]], 400);
        }

        $resultDeal = $this->zohoService->createDeal($request, $accountId);

        $code = $resultDeal['code'] ?? '';
        $dealId = $resultDeal['details']['id'] ?? null;

        if ($code !== 'SUCCESS' || !$dealId)  {
            return response()->json(['data' => [
                'message' => $resultDeal['message'],
                'status' => $resultDeal['status'],
            ]], 400);
        }

        $resultDeal = $this->zohoService->getDeal($dealId);

        $result = [
            'id' => $resultDeal['id'],
            'deal_name' => $resultDeal['Deal_Name'],
            'stage' => $resultDeal['Stage'],
            'account_name' => $resultDeal['Account_Name']['name'] ?? null,
            'account_id' => $resultDeal['Account_Name']['id'] ?? null,
            'created_time' => $resultDeal['Created_Time'],
            'modified_time' => $resultDeal['Modified_Time'],
            'message' => $resultDeal['Message'] ?? 'OK',
        ];

        return response()->json([
            'data' => $result,
        ], 201);

    }
}
