<?php

namespace App\Services\Concerns;

use RuntimeException;

/**
 * Cell-level edits on a worksheet's XML. Each cell keeps its style index
 * (so fonts, borders, number formats and merged ranges are untouched);
 * only the value changes. Strings are written inline, so the shared string
 * table (which other cells may share) is never modified.
 */
trait WritesXlsxCells
{
    private function xmlEscape(string $text): string
    {
        // Strip characters XML 1.0 cannot represent before escaping.
        $text = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text) ?? '';

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Rewrites one cell. $build receives the cell's style index (or null)
     * and the current inner XML, and returns the replacement <c> element.
     *
     * @param callable(?string, string): string $build
     */
    private function rewriteCell(string $xml, string $ref, callable $build): string
    {
        $count = 0;
        $result = preg_replace_callback(
            '/<c r="' . preg_quote($ref, '/') . '"([^>\/]*)(?:\/>|>(.*?)<\/c>)/s',
            function (array $m) use ($build) {
                $style = preg_match('/\ss="(\d+)"/', $m[1], $sm) ? $sm[1] : null;

                return $build($style, $m[2] ?? '');
            },
            $xml,
            1,
            $count
        );

        if ($count === 0 || $result === null) {
            throw new RuntimeException("Application Form template has no cell {$ref}.");
        }

        return $result;
    }

    private function cellOpen(string $ref, ?string $style): string
    {
        return '<c r="' . $ref . '"' . ($style !== null ? ' s="' . $style . '"' : '');
    }

    private function blankCell(string $xml, string $ref): string
    {
        return $this->rewriteCell($xml, $ref, fn (?string $s) => $this->cellOpen($ref, $s) . '/>');
    }

    /**
     * Writes text, or blanks the cell when the text is empty. Pass a style
     * to restyle the cell (used to highlight a selected option).
     */
    private function putText(string $xml, string $ref, ?string $text, ?string $style = null): string
    {
        $text = $text === null ? '' : trim($text);

        return $this->rewriteCell($xml, $ref, function (?string $s) use ($ref, $text, $style) {
            $s = $style ?? $s;

            return $text === ''
                ? $this->cellOpen($ref, $s) . '/>'
                : $this->cellOpen($ref, $s) . ' t="inlineStr"><is><t xml:space="preserve">' . $this->xmlEscape($text) . '</t></is></c>';
        });
    }

    private function putNumber(string $xml, string $ref, int|float|string|null $number): string
    {
        return $this->rewriteCell($xml, $ref, function (?string $s) use ($ref, $number) {
            return ($number === null || $number === '')
                ? $this->cellOpen($ref, $s) . '/>'
                : $this->cellOpen($ref, $s) . '><v>' . $number . '</v></c>';
        });
    }

    /**
     * Numeric-looking values become numbers (so Excel can total them);
     * anything else (e.g. "N.A.", "0123") stays text.
     */
    private function putSmart(string $xml, string $ref, ?string $value): string
    {
        $value = $value === null ? '' : trim($value);

        if (preg_match('/^(0|[1-9]\d{0,14})(\.\d+)?$/', $value)) {
            return $this->putNumber($xml, $ref, $value);
        }

        return $this->putText($xml, $ref, $value);
    }

    private function putFormula(string $xml, string $ref, string $formula, int|float|string $cached): string
    {
        return $this->rewriteCell($xml, $ref, fn (?string $s) => $this->cellOpen($ref, $s) . '><f>' . $this->xmlEscape($formula) . '</f><v>' . $cached . '</v></c>');
    }
}
