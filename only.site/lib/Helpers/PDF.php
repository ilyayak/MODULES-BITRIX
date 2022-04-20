<?php
/**
 * Класс дла создания PDF документов на основе TCPDF
 */

namespace Only\Site\Helpers;


class PDF
{
    public static function createDocument($html = 'Test documents')
    {
        $sDir = $_SERVER["DOCUMENT_ROOT"] . "/upload/tmp/";
        $sFileTempName = bitrix_sessid() . time() . ".pdf";
        $sFullPath = $sDir . $sFileTempName;
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Гостиница Ольга');
        $pdf->SetTitle('Заказ праздника под ключь');
        $pdf->SetSubject('Заказ праздника');
        //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        // set default font subsetting mode
        $pdf->setFontSubsetting(true);
        // set font
        $pdf->SetFont('freeserif', '', 12);
        // set color for text
        $pdf->SetTextColor(0, 0, 0);
        // add a page
        $pdf->AddPage();

        $pdf->writeHTML($html, true, 0, true, 0);
        // reset pointer to the last page
        $pdf->lastPage();
        //Close and output PDF document
        $pdf->Output($sFullPath, 'F');
        return $sFullPath;
    }

}
