<?php

namespace App\Service\Content;

class MarkdownConverter
{
    public function convert(string $markdown): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $markdown);
        $lines = explode("\n", $normalized);

        $htmlParts = [];
        $paragraphBuffer = [];
        $inUnorderedList = false;
        $inOrderedList = false;
        $inCodeBlock = false;
        $codeBuffer = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($this->isCodeFence($trimmed)) {
                if ($inCodeBlock) {
                    $htmlParts[] = sprintf('<pre><code>%s</code></pre>', $this->escape(implode("\n", $codeBuffer)));
                    $codeBuffer = [];
                    $inCodeBlock = false;
                } else {
                    $this->flushParagraph($htmlParts, $paragraphBuffer);
                    $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);
                    $inCodeBlock = true;
                }
                continue;
            }

            if ($inCodeBlock) {
                $codeBuffer[] = rtrim($line, "\n");
                continue;
            }

            if ($trimmed === '') {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);
                continue;
            }

            if ($this->isHeading($trimmed)) {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);
                $htmlParts[] = $this->convertHeading($trimmed);
                continue;
            }

            if ($this->isHorizontalRule($trimmed)) {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);
                $htmlParts[] = '<hr />';
                continue;
            }

            if ($this->isBlockquote($trimmed)) {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);
                $htmlParts[] = sprintf('<blockquote>%s</blockquote>', $this->convertInline(ltrim($trimmed, '> ')));
                continue;
            }

            if ($this->isUnorderedListItem($trimmed)) {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                if ($inOrderedList) {
                    $htmlParts[] = '</ol>';
                    $inOrderedList = false;
                }
                if (!$inUnorderedList) {
                    $htmlParts[] = '<ul>';
                    $inUnorderedList = true;
                }
                $htmlParts[] = sprintf('<li>%s</li>', $this->convertInline($this->extractListValue($trimmed)));
                continue;
            }

            if ($this->isOrderedListItem($trimmed)) {
                $this->flushParagraph($htmlParts, $paragraphBuffer);
                if ($inUnorderedList) {
                    $htmlParts[] = '</ul>';
                    $inUnorderedList = false;
                }
                if (!$inOrderedList) {
                    $htmlParts[] = '<ol>';
                    $inOrderedList = true;
                }
                $htmlParts[] = sprintf('<li>%s</li>', $this->convertInline($this->extractOrderedListValue($trimmed)));
                continue;
            }

            $paragraphBuffer[] = $line;
        }

        if ($inCodeBlock) {
            $htmlParts[] = sprintf('<pre><code>%s</code></pre>', $this->escape(implode("\n", $codeBuffer)));
        }

        $this->flushParagraph($htmlParts, $paragraphBuffer);
        $this->closeLists($htmlParts, $inUnorderedList, $inOrderedList);

        return implode("\n", array_filter($htmlParts, static fn (string $chunk): bool => $chunk !== ''));
    }

    private function isHeading(string $line): bool
    {
        return (bool) preg_match('/^(#{1,6})\s+.+$/', $line);
    }

    private function isHorizontalRule(string $line): bool
    {
        return (bool) preg_match('/^([-*_])\1{2,}$/', $line);
    }

    private function isBlockquote(string $line): bool
    {
        return str_starts_with($line, '>');
    }

    private function isUnorderedListItem(string $line): bool
    {
        return (bool) preg_match('/^[-*+]\s+.+$/', $line);
    }

    private function isOrderedListItem(string $line): bool
    {
        return (bool) preg_match('/^\d+\.\s+.+$/', $line);
    }

    private function extractListValue(string $line): string
    {
        return ltrim(substr($line, 1));
    }

    private function extractOrderedListValue(string $line): string
    {
        return (string) preg_replace('/^\d+\.\s+/', '', $line);
    }

    private function isCodeFence(string $line): bool
    {
        return (bool) preg_match('/^```/', $line);
    }

    private function flushParagraph(array &$htmlParts, array &$paragraphBuffer): void
    {
        if (empty($paragraphBuffer)) {
            return;
        }

        $text = implode("\n", $paragraphBuffer);
        $text = $this->convertInline($text);
        $text = str_replace("\n", "<br />\n", $text);
        $htmlParts[] = sprintf('<p>%s</p>', $text);
        $paragraphBuffer = [];
    }

    private function closeLists(array &$htmlParts, bool &$inUl, bool &$inOl): void
    {
        if ($inUl) {
            $htmlParts[] = '</ul>';
            $inUl = false;
        }

        if ($inOl) {
            $htmlParts[] = '</ol>';
            $inOl = false;
        }
    }

    private function convertHeading(string $line): string
    {
        preg_match('/^(#{1,6})\s+(.*)$/', $line, $matches);
        $level = strlen($matches[1]);
        $content = $this->convertInline($matches[2]);

        return sprintf('<h%d>%s</h%d>', $level, $content, $level);
    }

    private function convertInline(string $text): string
    {
        $escaped = $this->escape($text);

        $escaped = preg_replace_callback('/!\[(.*?)\]\(([^\s)]+)\)/', function (array $matches) {
            $alt = $matches[1];
            $src = $matches[2];

            return sprintf('<img src="%s" alt="%s" loading="lazy" />', $src, $alt);
        }, $escaped);

        $escaped = preg_replace_callback('/\[(.*?)\]\(([^\s)]+)\)/', function (array $matches) {
            $label = $matches[1];
            $href = $matches[2];

            return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', $href, $label);
        }, $escaped);

        $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped);
        $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
        $escaped = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $escaped);
        $escaped = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $escaped);
        $escaped = preg_replace('/_(.+?)_/s', '<em>$1</em>', $escaped);
        $escaped = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $escaped);

        return $escaped;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
