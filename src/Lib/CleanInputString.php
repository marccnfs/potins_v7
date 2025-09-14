<?php

namespace App\Lib;

class CleanInputString {

    /**
     * Methode 1 utilisant la conversion de l'encodage HTML
     *
     */
    static public function callHTMLEntities($initial): array|string|null
    {
        $s = htmlentities($initial);
        $s = preg_replace("/&(.)(acute|cedil|circ|ring|tilde|uml|grave);/", "$1", $s);
        $s = preg_replace('#&([A-za-z]{2})(?:lig);#', '1', $s); // pour les ligatures e.g. 'Å“'
        return $s;
    }

    /**
     * Methode 2 avec une table de remplacement
     */
    static public function callReplaceTable($initial): string
    {
        $TB_CONVERT = array(
            'Å ' => 'S', 'Å¡' => 's', 'Ã' => 'Dj', 'Å½' => 'Z', 'Å¾' => 'z', 'Ã€' => 'A', 'Ã' => 'A', 'Ã‚' => 'A', 'Ãƒ' => 'A', 'Ã„' => 'A',
            'Ã…' => 'A', 'Ã†' => 'A', 'Ã‡' => 'C', 'Ãˆ' => 'E', 'Ã‰' => 'E', 'ÃŠ' => 'E', 'Ã‹' => 'E', 'ÃŒ' => 'I', 'Ã' => 'I', 'ÃŽ' => 'I',
            'Ã' => 'I', 'Ã‘' => 'N', 'Ã’' => 'O', 'Ã“' => 'O', 'Ã”' => 'O', 'Ã•' => 'O', 'Ã–' => 'O', 'Ã˜' => 'O', 'Ã™' => 'U', 'Ãš' => 'U',
            'Ã›' => 'U', 'Ãœ' => 'U', 'Ã' => 'Y', 'Ãž' => 'B', 'ÃŸ' => 'Ss', 'Ã ' => 'a', 'Ã¡' => 'a', 'Ã¢' => 'a', 'Ã£' => 'a', 'Ã¤' => 'a',
            'Ã¥' => 'a', 'Ã¦' => 'a', 'Ã§' => 'c', 'Ã¨' => 'e', 'Ã©' => 'e', 'Ãª' => 'e', 'Ã«' => 'e', 'Ã¬' => 'i', 'Ã­' => 'i', 'Ã®' => 'i',
            'Ã¯' => 'i', 'Ã°' => 'o', 'Ã±' => 'n', 'Ã²' => 'o', 'Ã³' => 'o', 'Ã´' => 'o', 'Ãµ' => 'o', 'Ã¶' => 'o', 'Ã¸' => 'o', 'Ã¹' => 'u',
            'Ãº' => 'u', 'Ã»' => 'u', 'Ã¼'=>'u','Ã½' => 'y', 'Ã½' => 'y', 'Ã¾' => 'b', 'Ã¿' => 'y', 'Æ’' => 'f', 'Å“' => 'oe'
        );

        return strtr($initial, $TB_CONVERT);
    }

    /**
     * Supprimer les caractÃ¨res qui ne sont pas alphanumÃ©rique
     */
    static public function clean($initial): array|string|null
    {
        return preg_replace('/W+/', '-', $initial);
    }

    /**
     * Verifier la conversion Ã  partir d'un mot comportant un accent
     * @param $initial
     * @param $rewrite
     * @return boolean
     */
    static public function checkConversion($initial, $rewrite): bool
    {
        //Longueur du mot avant conversion
        $len_initial = strlen(utf8_decode($initial));
        //Longueur du mot aprÃ¨s conversion
        $len_rewrite = strlen(utf8_decode($rewrite));
        //cas particulier pour 'oe' exemple dans coeur
        if (strpos($initial, 'Å“') !== false) {
            $len_initial++;
        }
        //test de la longueur identique
        if ($len_initial != $len_rewrite) {
            return false;
        }
        //test de la prÃ©sence de caractÃ¨res interdits
        if (!preg_match('/^[A-Za-z0-9-\s\'.]+$/', $rewrite)) {
            return false;
        }

        return true;
    }

}