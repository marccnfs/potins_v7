<?php

namespace App\Twig;

use App\Service\Content\MarkdownConverter;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function __construct(private readonly MarkdownConverter $converter)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown_to_html', [$this, 'convert'], ['is_safe' => ['html']]),
        ];
    }

    public function convert(?string $value): Markup
    {
        if ($value === null) {
            return new Markup('', 'UTF-8');
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return new Markup('', 'UTF-8');
        }

        if ($this->looksLikeHtml($trimmed)) {
            return new Markup($value, 'UTF-8');
        }

        return new Markup($this->converter->convert($value), 'UTF-8');
    }

    private function looksLikeHtml(string $content): bool
    {
        return (bool) preg_match('/<\/?[a-z][\s\S]*>/i', $content);
    }
}
