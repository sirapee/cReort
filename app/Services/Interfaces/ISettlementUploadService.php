<?php

namespace App\Services\Interfaces;

use App\Contracts\Responses\SettlementUploadResponse;
use App\Http\Requests\SettlementRarRequest;

interface ISettlementUploadService
{
    public function uploadSettlementRarFile(SettlementRarRequest $request): SettlementUploadResponse;

}
