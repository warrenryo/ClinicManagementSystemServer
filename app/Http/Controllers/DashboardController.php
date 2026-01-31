<?php

namespace App\Http\Controllers;

use App\DTO\Response\DashboardFilterDTO;
use App\Enums\FilterTimeIntervals;
use App\Response\ResponseHelper;
use App\Services\DashboardService\IDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(IDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function GetPurchaseOrderAtCostChart(Request $request)
    {
        $dto = new DashboardFilterDTO(
            startDate: $request->start_date,
            endDate: $request->end_date,
            dateTime: $request->date_time,
            filterTimeIntervals: $request->FilterTimeIntervals !== null
                ? FilterTimeIntervals::from((int) $request->FilterTimeIntervals)
                : null
        );

        $response = $this->dashboardService->GetPurchaseOrderAtCostChart($dto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentCharData(Request $request)
    {
        $dto = new DashboardFilterDTO(
            startDate: $request->start_date,
            endDate: $request->end_date,
            dateTime: $request->date_time,
            filterTimeIntervals: $request->FilterTimeIntervals !== null
                ? FilterTimeIntervals::from((int) $request->FilterTimeIntervals)
                : null
        );

        $response = $this->dashboardService->GetAppointmentCharData($dto);
        return ResponseHelper::getStatusResponse($response);
    }

    public function generateClinicSummary(Request $request)
    {
        $dto = new DashboardFilterDTO(
            startDate: $request->StartDate,
            endDate: $request->EndDate,
            dateTime: $request->DateTime,
            filterTimeIntervals: $request->FilterTimeIntervals !== null
                ? FilterTimeIntervals::from((int) $request->FilterTimeIntervals)
                : null
        );

        $reponse = $this->dashboardService->generateClinicSummary($dto);
        return ResponseHelper::getStatusResponse($reponse);
    }

    public function GetLatestAISummary()
    {
        $response = $this->dashboardService->GetLatestAISummary();
        return ResponseHelper::getStatusResponse($response);
    }

    public function GetAppointmentReasonDistribution(Request $request)
    {
        $dto = new DashboardFilterDTO(
            startDate: $request->StartDate,
            endDate: $request->EndDate,
            dateTime: $request->DateTime,
            filterTimeIntervals: $request->FilterTimeIntervals !== null
                ? FilterTimeIntervals::from((int) $request->FilterTimeIntervals)
                : null
        );

        $reponse = $this->dashboardService->GetAppointmentReasonDistribution($dto);
        return ResponseHelper::getStatusResponse($reponse);
    }

    public function GetAppointmentReasonTrend(Request $request)
    {
        $dto = new DashboardFilterDTO(
            startDate: $request->StartDate,
            endDate: $request->EndDate,
            dateTime: $request->DateTime,
            filterTimeIntervals: $request->FilterTimeIntervals !== null
                ? FilterTimeIntervals::from((int) $request->FilterTimeIntervals)
                : null
        );

        $reponse = $this->dashboardService->GetAppointmentReasonsTrend($dto);
        return ResponseHelper::getStatusResponse($reponse);
    }
}
