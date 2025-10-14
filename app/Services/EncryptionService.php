<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    public function encrypt($value)
    {
        return Crypt::encryptString($value);
    }

    public function decrypt($value)
    {
        return Crypt::decryptString($value);
    }
}
