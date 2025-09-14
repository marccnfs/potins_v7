<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class isPlayerExtension extends AbstractExtension
{
	public function getFunctions(): array
    {
		return [
			new TwigFunction('isPlayer', [$this, 'isPlayer']),
		];

	}

	public function isPlayer($idcustomer, $tabpalyer): bool
    {
        return (in_array($idcustomer, $tabpalyer,false));
	}
}