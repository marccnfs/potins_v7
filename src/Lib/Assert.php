<?php

namespace App\Lib;

class Assert {
    
    public static function isValidEmail($email)
    {
        return preg_match('#^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$#', $email);
    }
    
    public static function isValidPostalCode($postalCode)
    {
        return preg_match('#^[0-9]{4,5}$#', $postalCode);
    }
    
    public static function isValidURL($url)
    {        
        return preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $url);
    }
    
    public static function isValidDate($date) {
        return preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/', $date);
    }
}