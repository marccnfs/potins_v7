<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class ActivityExtension extends AbstractExtension
{
	public function getFunctions(): array
    {
		return [
			new TwigFunction('isActivity', [$this, 'isActivity']),
		];

	}

	public function isActivity($module, $tabactiviy): bool
    {
        return (in_array($module, $tabactiviy,false));
	}
}