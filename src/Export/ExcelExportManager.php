<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Beefeater\CrudEventBundle\Model\PaginatedResult;

class ExcelExportManager
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function export(Request $request, PaginatedResult $paginatedResult, string $resourceName): ?Response
    {
        if ($request->attributes->get('_operation') !== 'L') {
            return null;
        }

        $contentTypeHeader = $request->headers->get('Accept');
        if ($contentTypeHeader !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return null;
        }

        $fileName = sprintf('%s.%s.xlsx', (new \DateTime())->format('Y-m-d'), $resourceName);

        $itemsForExport = $paginatedResult->getItems();

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $serializedItemsAsArrays = [];
        foreach ($itemsForExport as $entityObject) {
            $serializedItemsAsArrays[] = $this->serializer->normalize($entityObject);
        }

        $allRowsForExcel = [];
        foreach ($serializedItemsAsArrays as $serializedItem) {
            $rowsFromItem = $this->convertItemToRows($serializedItem);
            $allRowsForExcel = array_merge($allRowsForExcel, $rowsFromItem);
        }

        $columnHeaders = [];
        $isHeaderRowWritten = false;
        $rowIndexForExcel = 2;

        foreach ($allRowsForExcel as $excelRowData) {
            if (!$isHeaderRowWritten) {
                $columnIndexForExcel = 1;
                foreach ($excelRowData as $columnKey => $columnValue) {
                    $columnHeaders[] = $columnKey;
                    $cellCoordinate = Coordinate::stringFromColumnIndex($columnIndexForExcel) . '1';
                    $activeSheet->setCellValue($cellCoordinate, $columnKey);
                    $activeSheet->getStyle($cellCoordinate)->getFont()->setBold(true);
                    $activeSheet->getColumnDimension(Coordinate::stringFromColumnIndex($columnIndexForExcel))
                        ->setAutoSize(true);
                    $columnIndexForExcel++;
                }
                $isHeaderRowWritten = true;
            }

            $columnIndexForExcel = 1;
            foreach ($columnHeaders as $columnKey) {
                $cellValue = $excelRowData[$columnKey] ?? null;
                $activeSheet->setCellValue(
                    Coordinate::stringFromColumnIndex($columnIndexForExcel)
                    . $rowIndexForExcel,
                    $cellValue
                );
                $columnIndexForExcel++;
            }

            $rowIndexForExcel++;
        }

        $excelWriter = new Xlsx($spreadsheet);
        ob_start();
        $excelWriter->save('php://output');
        $excelBinaryContent = ob_get_clean();

        return new Response(
            $excelBinaryContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
            ]
        );
    }

    private function convertItemToRows(array $array): array
    {
        $rowsForCurrentElement = [];
        $baseDataForCurrentElement = [];

        foreach ($array as $fieldName => $fieldValue) {
            if (!is_array($fieldValue)) {
                $baseDataForCurrentElement[$fieldName] = $fieldValue;
                continue;
            }

            if ($this->isAssociativeArray($fieldValue)) {
                foreach ($fieldValue as $subFieldName => $subFieldValue) {
                    $baseDataForCurrentElement[$fieldName . '_' . $subFieldName] = $subFieldValue;
                }
                continue;
            }

            $rowsFromNestedArray = [];
            foreach ($fieldValue as $nestedObject) {
                $rowForNestedObject = $baseDataForCurrentElement;
                foreach ($nestedObject as $nestedFieldName => $nestedFieldValue) {
                    $rowForNestedObject[$fieldName . '_' . $nestedFieldName] = $nestedFieldValue;
                }
                $rowsFromNestedArray[] = $rowForNestedObject;
            }

            if (!empty($rowsFromNestedArray)) {
                $rowsForCurrentElement = array_merge($rowsForCurrentElement, $rowsFromNestedArray);
            }
        }

        if (empty($rowsForCurrentElement)) {
            $rowsForCurrentElement[] = $baseDataForCurrentElement;
        } else {
            foreach ($rowsForCurrentElement as &$row) {
                $row = array_merge($baseDataForCurrentElement, $row);
            }
        }

        return $rowsForCurrentElement;
    }

    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
