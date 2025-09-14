<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MontantTvaExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return array(new TwigFilter('montantTva', array($this,'montantTvaFilter')));
    }

    function montantTvaFilter($prixHT,$tva): float
    {
        return round((($prixHT / $tva) - $prixHT),2);
    }

    public function getName(): string
    {
        return 'montant_tva_extension';
    }
}