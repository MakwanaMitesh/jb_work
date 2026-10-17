<?php

namespace App\Services\Concerns;

use ZipArchive;

trait RecalculatesXlsxOnOpen
{
    /**
     * Formulas are rewritten (or emptied) cell by cell, but the calc chain
     * still lists the original formula cells; Excel treats a stale chain as
     * a corrupt file. Drop it (Excel rebuilds it) and recalculate on open.
     */
    private function forceRecalculation(ZipArchive $zip): void
    {
        $zip->deleteName('xl/calcChain.xml');

        $types = (string) $zip->getFromName('[Content_Types].xml');
        $zip->addFromString('[Content_Types].xml', preg_replace('/<Override [^>]*calcChain[^>]*\/>/', '', $types));

        $rels = (string) $zip->getFromName('xl/_rels/workbook.xml.rels');
        $zip->addFromString('xl/_rels/workbook.xml.rels', preg_replace('/<Relationship [^>]*calcChain[^>]*\/>/', '', $rels));

        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $zip->addFromString('xl/workbook.xml', preg_replace('/<calcPr ([^>]*?)\/>/', '<calcPr $1 fullCalcOnLoad="1"/>', $workbook));
    }
}
