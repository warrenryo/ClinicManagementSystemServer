<?php

namespace App\Helpers;

use App\Models\Auth\UserDetails;

class UserHelper
{
    public static function getUserDetailsId()
    {
        $userId = Token::getUserId();

        if (!$userId) {
            return null;
        }

        return UserDetails::where('user_id', $userId)
            ->value('id');
    }
}
