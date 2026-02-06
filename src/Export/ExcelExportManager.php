<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Export;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Beefeater\CrudEventBundle\Model\PaginatedResult;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportManager
{
    public function export(
        Request $request,
        PaginatedResult $result,
        string $resourceName
    ): ?Response {
        if ($request->attributes->get('_operation') !== 'L') {
            return null;
        }

        if (!$request->attributes->get('_export', false)) {
            return null;
        }

        $contentType = $request->headers->get('Content-Type');
        if ($contentType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return null;
        }

        $items = $result->getItems();
        if (empty($items)) {
            throw new \RuntimeException('No items to export.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [];
        $firstRow = true;
        $rowIndex = 2;

        foreach ($items as $item) {
            if ($firstRow) {
                $colIndex = 1;
                foreach ($item as $key => $value) {
                    $columns[] = $key;
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . '1', $key);
                    $colIndex++;
                }
                $firstRow = false;
            }
            $colIndex = 1;
            foreach ($columns as $colName) {
                $value = $item[$colName] ?? null;

                if (is_scalar($value) || is_null($value)) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $rowIndex, $value);
                } elseif (is_array($value)) {
                    if (isset($value['id'])) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $rowIndex, $value['id']);
                    } else {
                        $ids = [];
                        foreach ($value as $v) {
                            if (is_array($v) && isset($v['id'])) {
                                $ids[] = $v['id'];
                            }
                        }
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $rowIndex, implode(',', $ids));
                    }
                } else {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($colIndex) . $rowIndex, '');
                }

                $colIndex++;
            }

            $rowIndex++;
        }

        $filename = sprintf(
            '%s.%s.xlsx',
            (new \DateTime())->format('Y-m-d'),
            $resourceName
        );

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        return new Response(
            $excelContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ]
        );
    }
}
