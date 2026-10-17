<?php

namespace App\Services\Concerns;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Table/paragraph helpers shared by the .docx generators: locate a table or
 * paragraph by its static text, and rewrite a cell's value while keeping the
 * formatting of the text it replaces.
 */
trait FillsDocxTables
{
    private function findParagraphContaining(DOMXPath $xpath, string $needle): ?DOMElement
    {
        foreach ($xpath->query('//w:p') as $p) {
            if (str_contains($this->cellText($xpath, $p), $needle)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Rewrites a cell's text: removes its runs and, when there is text,
     * adds one run carrying the given run properties (highlight dropped).
     */
    private function putCellText(DOMDocument $dom, DOMXPath $xpath, ?\DOMNode $cell, ?string $text, ?DOMElement $rPr): void
    {
        if (! $cell instanceof DOMElement) {
            return;
        }

        // The value goes back into the paragraph that held the old one (cells whose
        // text sat below spacer paragraphs keep their alignment).
        $paragraph = $xpath->query('./w:p[w:r]', $cell)->item(0) ?? $xpath->query('./w:p', $cell)->item(0);

        foreach (iterator_to_array($xpath->query('.//w:r', $cell)) as $run) {
            $run->parentNode->removeChild($run);
        }

        $text = $text === null ? '' : trim($text);
        if ($text === '' || ! $paragraph instanceof DOMElement) {
            return;
        }

        $run = $dom->createElementNS($this->wordNs(), 'w:r');
        if ($rPr) {
            $props = $rPr->cloneNode(true);
            foreach (iterator_to_array($props->childNodes) as $child) {
                if ($child instanceof DOMElement && $child->localName === 'highlight') {
                    $props->removeChild($child);
                }
            }
            $run->appendChild($props);
        }
        $t = $dom->createElementNS($this->wordNs(), 'w:t');
        $t->appendChild($dom->createTextNode($this->oneLine($text)));
        $t->setAttribute('xml:space', 'preserve');
        $run->appendChild($t);
        $paragraph->appendChild($run);
    }

    /**
     * Run properties per column of a (demo) data row: the first run's, else the paragraph mark's.
     *
     * @return array<int, ?DOMElement>
     */
    private function columnRunProps(DOMXPath $xpath, DOMElement $row): array
    {
        $out = [];
        foreach ($xpath->query('./w:tc', $row) as $c => $cell) {
            $out[$c] = $this->firstRunProps($xpath, $cell);
        }

        // Cells that were empty in the demo borrow the nearest populated column's formatting.
        $fallback = collect($out)->first(fn ($v) => $v !== null);
        foreach ($out as $c => $v) {
            $out[$c] = $v ?? $fallback;
        }

        return $out;
    }

    private function firstRunProps(DOMXPath $xpath, ?\DOMNode $cell): ?DOMElement
    {
        if (! $cell instanceof DOMElement) {
            return null;
        }

        $rPr = $xpath->query('.//w:r/w:rPr', $cell)->item(0) ?? $xpath->query('./w:p/w:pPr/w:rPr', $cell)->item(0);

        // A detached copy: the runs it came from are removed from the document later.
        return $rPr instanceof DOMElement ? $rPr->cloneNode(true) : null;
    }

    private function oneLine(?string $text): string
    {
        return trim((string) preg_replace('/\s*[\r\n]+\s*/', ', ', (string) $text));
    }

    /**
     * @param array<int, ?string> $parts
     */
    private function joinNonEmpty(string $glue, array $parts): string
    {
        return implode($glue, array_filter($parts, fn ($p) => $p !== null && trim($p) !== ''));
    }

    private function findTableContaining(DOMXPath $xpath, string $needle): ?DOMElement
    {
        foreach ($xpath->query('//w:tbl') as $tbl) {
            if (str_contains($this->concatCellsText($xpath, $xpath->query('.//w:tc', $tbl)), $needle)) {
                return $tbl;
            }
        }

        return null;
    }
}
