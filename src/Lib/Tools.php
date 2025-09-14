<?php

namespace App\Lib;

class Tools {


    /**
     * Supprimer les caractÃ¨res qui ne sont pas alphanumÃ©rique
     */
    static public function clean($initial): array|string|null
    {
        $str=strtolower(trim($initial));
        $str=strip_tags($str);
        $str = preg_replace('/W+/', '-', $str);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
        $str = str_replace( array( '\'', '"', ';', '@', '!', '?', '%', '&','#','(',')','$','£','{','}','~','>','<','=' ), '', $str);
        $str = str_replace( array( ',,' ), ',', $str);
        // return utf8_encode($str);

        return tools::fctRetirerAccents($str);

    }


    public static function fctRetirerAccents($varMaChaine): array|string
    {
        $search  = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ð', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ');
        //Préférez str_replace à strtr car strtr travaille directement sur les octets, ce qui pose problème en UTF-8
        $replace = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y');

        return str_replace($search, $replace, $varMaChaine); //On retourne le résultat
    }
        
    public static function stripAccents($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        // supprime les autres caractères

        return preg_replace('#&[^;]+;#', '', $str);
    }

    public static function cleanTags($str, $charset='utf-8')
    {
        $str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
        $str = str_replace( array( '\'', '"', ';', '@', '!', '?', '%', '&','#','(',')','$','£','{','}','~','>','<','=' ), '', $str);
        $str = str_replace( array( ',,' ), ',', $str);
        $str = preg_replace('/\s\s+/', '', $str);
        if(str_ends_with($str, ",")){
           $str = substr_replace ($str,"",-1,1);
        }
        if(substr($str,0)===","){
            $str = substr_replace ($str,"",0,1);
        }
        $str=strtolower(trim($str));
        $tabtag=explode(",",$str);
        foreach ($tabtag as &$tag){
            if(strlen($str)>20){
                $tag=substr($tag,0,20);
            }
        }
        return $tabtag;
    }

    public static function cleaninput($str, $charset='utf-8')
    {
        //$str = htmlentities($str, ENT_NOQUOTES, $charset);

        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
        $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
        //$str = str_replace( array( '\'', '"', ';', '@', '!', '?', '%', '&','#','(',')','$','£','{','}','~','>','<','=' ), '', $str);
        $str = str_replace( array( '\'','{','}','~','>','<','=' ), '', $str);
        $str = str_replace( array( ',,' ), ',', $str);
        return preg_replace('/\s\s+/', '', $str);
    }
    
    public static function decodeBBCode($string): array|string|null
    {
        return preg_replace('`\[lien\](.+)\[/lien\]`isU', '<a href="$1">$1</a>', $string);
    }
    
    public static function encodeBBCode($string): array|string|null
    {
        
        $string = preg_replace('`\<br>`isU', '', $string);
        $string = preg_replace('`\<br(.*)\/>`isU', '', $string);
        $string = preg_replace('`\<a href="(.*)"(.*)>(.*)<\/a>`isU', '[lien]$1[/lien]', $string);
        
        return $string;
    }
    public static function genererChaineAleatoire($longueur = 24){
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($longueur/strlen($x)) )),1,$longueur);
    }

    public static function cleanspecialcaractere($text, $charset='utf-8') {
        $text = htmlentities($text, ENT_NOQUOTES, $charset);
        //$text  = preg_replace('/[^a-zA-Z0-9_ -]/s','',$text);
        //$text = str_replace( array( '\'', '"', ';', '<', '>' ), ' ', $text);
        return $text;
        /*
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '//' => '-', // conversion d'un tiret UTF-8 en un tiret simple
            '/[]/u' => ' ', // guillemet simple
            '/[«»]/u' => ' ', // guillemet double
            '/ /' => ' ', // espace insécable (équiv. à 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
        */
    }

    public static function cleaninput2($str): string
    {
        $str = trim($str);
        $str = stripslashes($str);
       // return htmlentities($str, ENT_NOQUOTES, "UTF-8");
        return htmlspecialchars($str);
    }

    /**
     * @param $string
     * @return false|string|string[]|void|null
     */
    public function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    public function updateCanonicalFields(User $user)
    {
        $user->setEmailCanonical($this->canonicalize($user->getEmail()));
    }


    public function canonicalizeEmail(User $user)
    {

        $user->setEmailCanonical($this->canonicalize($user->getEmail()));
    }

    public function check($site)
    {
        $site = curl_init($site);
        curl_setopt($site, CURLOPT_FAILONERROR, true);
        curl_setopt($site, CURLOPT_NOBODY, true);

        if (curl_exec($site) === false)
            $curl = false;
        else
            $curl = true;

        curl_close($site);

        return $curl;
    }

    public function findUrl($value)
    {
        $violation = false;
        $urls = preg_match_all('/<a href="(.*)">/', $value, $url);

        foreach(array_unique($url[1]) as $site)
        {
            if ($this->check($site) === false) {
                $violation = true;
            }
        }
        return $violation;
    }

    private $passwordEncoder;


    /**
     * @param $user User
     */
    public function hashPassword($user)
    {
        $plainpassword=$user->getPlainPassword();
        $user->setPassword($this->passwordEncoder->encodePassword($user, $plainpassword));
        $user->eraseCredentials();
    }
}