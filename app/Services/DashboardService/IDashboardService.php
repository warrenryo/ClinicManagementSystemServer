<?php

namespace App\Services\DashboardService;

use App\DTO\Response\DashboardFilterDTO;
use Illuminate\Http\Request;

interface IDashboardService
{
    public function GetPurchaseOrderAtCostChart(DashboardFilterDTO $request);
    public function GetAppointmentCharData(DashboardFilterDTO $request);
    public function generateClinicSummary(DashboardFilterDTO $request);
    public function GetLatestAISummary();
    public function GetAppointmentReasonDistribution(DashboardFilterDTO $request);
    public function GetAppointmentReasonsTrend(DashboardFilterDTO $request);
}
