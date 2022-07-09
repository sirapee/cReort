<?php


namespace App\Helpers;


class CheckTrustedUsers
{

    public static function checkTrustedUser($username, $password){
        if(!self::validateUser($username)) {
            throw new \RuntimeException("User Not Trusted!!!");
        }
        return self::validateLogin($username, $password);
    }

    private static function validateUser($username): bool
    {
        $trustedUsers = config('app.trusted_users');
        return in_array($username, $trustedUsers, true);
    }

    private static function validateLogin($username, $password): bool
    {
        return LoginHelper::CLILogin($username, $password);
    }
}

?>
