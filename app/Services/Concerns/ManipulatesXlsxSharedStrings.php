<?php

namespace App\Services\Concerns;

/**
 * Shared machinery for editing xl/sharedStrings.xml of an .xlsx template
 * in-place while leaving everything else (styles, formulas, merged cells,
 * column widths, printer settings, calc chain) untouched. Used by any
 * generator that copies a master .xlsx and rewrites only specific dynamic
 * text values.
 */
trait ManipulatesXlsxSharedStrings
{
    /**
     * Replaces a plain-text substring wherever it appears inside <t>...</t>
     * runs of sharedStrings.xml, XML-escaping the replacement so any special
     * characters in Lead data (&, <, >) don't corrupt the workbook.
     */
    private function replaceWithinTextTags(string $xml, string $needle, string $replacement): string
    {
        if ($needle === '') {
            return $xml;
        }

        $escapedReplacement = htmlspecialchars($replacement, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return preg_replace_callback(
            '/<t([^>]*)>(.*?)<\/t>/s',
            function (array $m) use ($needle, $escapedReplacement) {
                if (str_contains($m[2], $needle)) {
                    return '<t' . $m[1] . '>' . str_replace($needle, $escapedReplacement, $m[2]) . '</t>';
                }

                return $m[0];
            },
            $xml
        );
    }

    /**
     * Replaces the entire <t> content of the Nth <si> entry (0-based, matching
     * the shared-string index used by <c t="s"><v>N</v></c> cells) with the
     * given text, or clears it when the text is empty. Used only for indexes
     * confirmed to be single-purpose (not reused by any other cell).
     *
     * @param array<int, string> $indexToText
     */
    private function replaceByIndex(string $xml, array $indexToText): string
    {
        $i = -1;

        return preg_replace_callback(
            '/<si>(.*?)<\/si>/s',
            function (array $m) use (&$i, $indexToText) {
                $i++;

                if (! array_key_exists($i, $indexToText)) {
                    return $m[0];
                }

                $escaped = htmlspecialchars($indexToText[$i], ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $replacedOnce = false;

                $inner = preg_replace_callback(
                    '/<t([^>]*)>(.*?)<\/t>/s',
                    function (array $tm) use ($escaped, &$replacedOnce) {
                        if ($replacedOnce) {
                            return $tm[0];
                        }
                        $replacedOnce = true;

                        return '<t' . $tm[1] . '>' . $escaped . '</t>';
                    },
                    $m[1]
                );

                return '<si>' . $inner . '</si>';
            },
            $xml
        );
    }

    /**
     * Clears a specific worksheet cell (by its A1-style reference, e.g.
     * "B18") back to a bare self-closing tag: <c r="B18" s="121"/>. Keeps
     * the cell's style (borders/fonts/number format/column width all live
     * on the style index, not the cell) but drops any value, formula and
     * type attribute — so the cell renders as genuinely empty rather than
     * a stale cached number or a formula that would recompute to 0 against
     * blank inputs.
     */
    private function clearWorksheetCell(string $xml, string $ref): string
    {
        return preg_replace_callback(
            '/<c r="' . preg_quote($ref, '/') . '"([^>]*?)(?:\/>|>.*?<\/c>)/s',
            function (array $m) use ($ref) {
                $style = '';
                if (preg_match('/\ss="(\d+)"/', $m[1], $sm)) {
                    $style = ' s="' . $sm[1] . '"';
                }

                return '<c r="' . $ref . '"' . $style . '/>';
            },
            $xml
        );
    }
}
