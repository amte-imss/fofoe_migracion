<?php

namespace App\Controller\Api;

use App\Controller\DIEControllerController;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class UtilsController extends DIEControllerController
{
    #[Route('/add-dates-years', name: 'api.utils.add-dates-years', methods: ['POST'])]
    public function addDatesYears(Request $request): JsonResponse
    {
        $startDate = (string) $this->input($request, 'date', '');
        $incrementRaw = strtolower(trim((string) $this->input($request, 'increment', '')));

        if ($startDate === '') {
            return new JsonResponse(['message' => 'The "date" parameter is required.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $date = Carbon::parse($startDate);
        } catch (InvalidFormatException) {
            return new JsonResponse(['message' => 'Invalid "date" parameter.'], Response::HTTP_BAD_REQUEST);
        }

        $isMonthsIncrement = str_ends_with($incrementRaw, 'm');
        $incrementValue = (int) rtrim($incrementRaw, 'm');

        $result = $isMonthsIncrement
            ? $date->addMonths($incrementValue)
            : $date->addYears($incrementValue);

        return new JsonResponse([
            'data' => $result->format('Y-m-d'),
        ]);
    }
}
