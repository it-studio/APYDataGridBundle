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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExport extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    public $objPHPExcel;

    public function setup($title, $fileName = 'export', $params = [], $charset = 'UTF-8', $role = null)
    {
        $this->objPHPExcel = new Spreadsheet;

        parent::setup($title, $fileName, $params, $charset, $role);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatGridData($grid);

        $row = 1;
        foreach ($data as $line) {
            $column = 'A';
            foreach ($line as $cell) {
                $this->objPHPExcel->getActiveSheet()->setCellValue($column . $row, $cell);

                ++$column;
            }
            ++$row;
        }

        $objWriter = $this->getWriter();

        ob_start();

        $objWriter->save('php://output');

        $this->content = ob_get_contents();

        ob_end_clean();
    }

    protected function getWriter()
    {
        return new Xlsx($this->objPHPExcel);
    }
}
