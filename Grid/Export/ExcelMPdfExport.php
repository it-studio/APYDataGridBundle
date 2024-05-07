<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Export;

use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class ExcelMPdfExport extends ExcelPdfExport
{

    protected function getWriter()
    {
        $writer = new Mpdf($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
