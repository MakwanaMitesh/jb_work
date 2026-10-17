<?php

namespace App\Services\Concerns;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Shared machinery for editing word/document.xml of a .docx template
 * in-place while leaving everything else (styles, formatting, tables,
 * images, headers/footers, static wording) untouched. Used by any
 * generator that copies a master .docx and rewrites only specific
 * dynamic values.
 */
trait ManipulatesDocxRuns
{
    /**
     * Finds $needle within a paragraph's VISIBLE text even when Word has
     * split it across several differently-formatted <w:r> runs (kerning
     * adjustments, spell-check boundaries, etc. routinely fragment a single
     * phrase run-by-run), and replaces it. The match may span multiple
     * runs: the first and last matched runs are trimmed to keep their
     * unmatched prefix/suffix text, every run fully inside the match is
     * removed, and a single new run — formatted like the first matched run —
     * is inserted with the replacement text (an empty replacement simply
     * removes the matched text, leaving any static wrapper text around it
     * intact — used to blank out a value when there's no data for it,
     * rather than leaving the template's demo value in place). Runs
     * containing a <w:br/>, <w:tab/>, drawing, etc. are treated as hard
     * boundaries a match can never cross, so line breaks and non-text
     * content are never disturbed.
     */
    private function replacePhraseAcrossRuns(DOMDocument $dom, DOMXPath $xpath, string $needle, string $replacement): void
    {
        if ($needle === '') {
            return;
        }

        foreach ($xpath->query('//w:p') as $p) {
            // Group the paragraph's children into runs of contiguous
            // "simple" <w:r> (single <w:t>, no br/tab/drawing) segments.
            $segments = [];
            $current = [];

            foreach (iterator_to_array($p->childNodes) as $node) {
                if ($this->isTransparentMarker($node)) {
                    continue; // spell-check/bookmark markers carry no text — ignore, don't break the segment
                }

                $t = $this->simpleRunText($node);
                if ($t === null) {
                    if ($current) {
                        $segments[] = $current;
                        $current = [];
                    }

                    continue;
                }
                $current[] = ['run' => $node, 'text' => $t];
            }
            if ($current) {
                $segments[] = $current;
            }

            foreach ($segments as $segment) {
                $this->replaceWithinSegment($dom, $segment, $needle, $replacement);
            }
        }
    }

    /**
     * Word scatters invisible spell-check/grammar-check/bookmark markers
     * between runs (e.g. <w:proofErr w:type="spellStart"/> right in the
     * middle of a phrase). They carry no text and no rendering effect, so
     * they must not be treated as boundaries that stop a phrase from being
     * matched across the runs on either side of them.
     */
    private function isTransparentMarker($node): bool
    {
        return $node instanceof DOMElement && in_array($node->localName, [
            'proofErr', 'bookmarkStart', 'bookmarkEnd', 'commentRangeStart', 'commentRangeEnd',
        ], true);
    }

    /**
     * Returns the run's text if it's a "simple" run (exactly one <w:t>,
     * nothing else), or null if it's not a run at all or isn't simple
     * (contains a break/tab/drawing/etc., which must not be merged across).
     */
    private function simpleRunText($node): ?string
    {
        if (! $node instanceof DOMElement || $node->localName !== 'r') {
            return null;
        }

        $t = null;
        foreach ($node->childNodes as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }
            if ($child->localName === 'rPr') {
                continue;
            }
            if ($child->localName === 't' && $t === null) {
                $t = $child;

                continue;
            }

            return null; // anything else (br, tab, drawing, a 2nd <w:t>) — not simple
        }

        return $t?->textContent;
    }

    /**
     * @param list<array{run: DOMElement, text: string}> $segment
     */
    private function replaceWithinSegment(DOMDocument $dom, array $segment, string $needle, string $replacement): void
    {
        $offsets = [];
        $pos = 0;
        $concat = '';
        foreach ($segment as $i => $part) {
            $offsets[$i] = $pos;
            $concat .= $part['text'];
            $pos += mb_strlen($part['text']);
        }

        $matchPos = mb_strpos($concat, $needle);
        if ($matchPos === false) {
            return;
        }
        $matchEnd = $matchPos + mb_strlen($needle);

        $firstIdx = null;
        $lastIdx = null;
        foreach ($segment as $i => $part) {
            $start = $offsets[$i];
            $end = $start + mb_strlen($part['text']);
            if ($firstIdx === null && $matchPos < $end && $matchPos >= $start) {
                $firstIdx = $i;
            }
            if ($matchEnd > $start && $matchEnd <= $end) {
                $lastIdx = $i;
            }
        }
        if ($firstIdx === null || $lastIdx === null) {
            return;
        }

        $firstPart = $segment[$firstIdx];
        $lastPart = $segment[$lastIdx];
        $prefix = mb_substr($firstPart['text'], 0, $matchPos - $offsets[$firstIdx]);
        $suffix = mb_substr($lastPart['text'], $matchEnd - $offsets[$lastIdx]);

        // Trim the first run to its prefix and the last run to its
        // suffix (same run when the match sits entirely inside one),
        // remove any runs fully consumed by the match in between, then
        // insert one new run — formatted like the first matched run —
        // holding the replacement text between the trimmed prefix/suffix.
        for ($i = $firstIdx + 1; $i < $lastIdx; $i++) {
            $segment[$i]['run']->parentNode->removeChild($segment[$i]['run']);
        }

        $newRun = $replacement !== '' ? $this->cloneRunWithText($dom, $firstPart['run'], $replacement) : null;

        if ($lastIdx === $firstIdx) {
            $this->setRunText($firstPart['run'], $prefix);
            $anchor = $firstPart['run'];
            if ($newRun) {
                $firstPart['run']->parentNode->insertBefore($newRun, $firstPart['run']->nextSibling);
                $anchor = $newRun;
            }
            if ($suffix !== '') {
                $suffixRun = $this->cloneRunWithText($dom, $firstPart['run'], $suffix);
                $anchor->parentNode->insertBefore($suffixRun, $anchor->nextSibling);
            }
        } else {
            $this->setRunText($firstPart['run'], $prefix);
            $this->setRunText($lastPart['run'], $suffix);
            if ($newRun) {
                $firstPart['run']->parentNode->insertBefore($newRun, $firstPart['run']->nextSibling);
            }
        }
    }

    private function setRunText(DOMElement $run, string $text): void
    {
        foreach ($run->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 't') {
                $child->textContent = $text;
                if ($text !== '' && (ctype_space($text[0] ?? '') || ctype_space(substr($text, -1) ?: ''))) {
                    $child->setAttribute('xml:space', 'preserve');
                }
            }
        }
    }

    private function cloneRunWithText(DOMDocument $dom, DOMElement $templateRun, string $text): DOMElement
    {
        $run = $dom->createElementNS($this->wordNs(), 'w:r');

        foreach ($templateRun->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'rPr') {
                $run->appendChild($child->cloneNode(true));
            }
        }

        $t = $dom->createElementNS($this->wordNs(), 'w:t');
        $t->appendChild($dom->createTextNode($text));
        $t->setAttribute('xml:space', 'preserve');
        $run->appendChild($t);

        return $run;
    }

    private function concatCellsText(DOMXPath $xpath, \DOMNodeList $cells): string
    {
        $parts = [];
        foreach ($cells as $cell) {
            $parts[] = $this->cellText($xpath, $cell);
        }

        return implode(' ', array_filter($parts, fn ($p) => $p !== ''));
    }

    private function cellText(DOMXPath $xpath, $cell): string
    {
        $text = '';
        foreach ($xpath->query('.//w:t', $cell) as $t) {
            $text .= $t->textContent;
        }

        return $text;
    }

    /**
     * Writes $text into the first <w:p> of $cell, using the paragraph's own
     * pPr formatting (unchanged) plus a new <w:r><w:t> run. Only called when
     * the cell is empty (or the caller explicitly wants to override it).
     */
    private function setFirstParagraphText(DOMDocument $dom, DOMXPath $xpath, DOMElement $cell, string $text, bool $overwrite = false): void
    {
        $paragraphs = $xpath->query('./w:p', $cell);
        if ($paragraphs->length === 0) {
            return;
        }

        /** @var DOMElement $firstParagraph */
        $firstParagraph = $paragraphs->item(0);

        if ($overwrite) {
            foreach ($xpath->query('./w:r', $firstParagraph) as $existingRun) {
                $firstParagraph->removeChild($existingRun);
            }
        }

        $run = $dom->createElementNS($this->wordNs(), 'w:r');
        $t = $dom->createElementNS($this->wordNs(), 'w:t', htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
        $t->setAttribute('xml:space', 'preserve');
        $run->appendChild($t);
        $firstParagraph->appendChild($run);
    }

    /**
     * Removes every <w:highlight> run property from the document, wherever
     * it appears — used once at the end of generation to strip the master
     * template's yellow "this is a dynamic field" markers from the final
     * output, regardless of whether that field's text was replaced, left
     * static, or blanked.
     */
    private function stripAllHighlighting(DOMXPath $xpath): void
    {
        foreach (iterator_to_array($xpath->query('//w:highlight')) as $highlight) {
            $highlight->parentNode->removeChild($highlight);
        }
    }

    private function wordNs(): string
    {
        return 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    }
}
