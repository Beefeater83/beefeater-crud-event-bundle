<?php

declare(strict_types=1);

namespace Beefeater\CrudEventBundle\Export;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Beefeater\CrudEventBundle\Model\PaginatedResult;

class ExportManager
{
    public function export(
        Request $request,
        PaginatedResult $result,
        string $resourceName
    ): ?JsonResponse {
        if ($request->attributes->get('_operation') !== 'L') {
            return null;
        }
        $export = $request->attributes->get('_export', []);
        if (empty($export)) {
            return null;
        }

        $contentType = $request->headers->get('Content-Type');

        if (($export['excel'] ?? false) && $contentType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return new JsonResponse([
                'message' => ''
            ]);
        }

        return null;
    }
}
