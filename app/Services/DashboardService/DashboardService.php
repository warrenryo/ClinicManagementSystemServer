<?php

namespace App\Services\DashboardService;

use App\DTO\Response\DashboardFilterDTO;
use App\Enums\AppointmentReasons;
use App\Enums\AppointmentStatus;
use App\Enums\ApprovalStatus;
use App\Helpers\DashboardDateHelper;
use App\Models\Gemini\AISummary;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use App\Services\GeminiService\IGeminiService;
use Carbon\Carbon;

class DashboardService implements IDashboardService
{
    protected $geminiService;
    public function __construct(IGeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function GetPurchaseOrderAtCostChart(DashboardFilterDTO $request): array
    {
        try {
            // 1. Get periods (same logic as .NET)
            $periods = DashboardDateHelper::getChartPeriods($request);
            $datePeriods = DashboardDateHelper::getPeriods($request);

            $purchaseOrders = PurchaseOrder::with('purchaseOrderItems')
                ->where('approval_status', ApprovalStatus::RECEIVED->value) // optional
                ->where('created_at', '>=', $datePeriods['current_start'])
                ->where('created_at', '<', $datePeriods['current_end'])
                ->get();

            $chartData = [];
            $labels = [];

            foreach ($periods as $period) {
                $start = $period['start'];
                $end   = $period['end'];
                $label = $period['label'];

                $totalAtCost = $purchaseOrders
                    ->filter(
                        fn($po) =>
                        $po->created_at >= $start && $po->created_at < $end
                    )
                    ->flatMap->purchaseOrderItems
                    ->sum(fn($item) => $item->quantity * $item->at_cost);

                $chartData[] = round($totalAtCost, 2);
                $labels[] = $label;
            }

            $totalCurrent = array_sum($chartData);

            $totalPrevious = PurchaseOrder::with('purchaseOrderItems')
                ->where('approval_status', ApprovalStatus::RECEIVED->value)
                ->where('created_at', '>=', $datePeriods['prev_start'])
                ->where('created_at', '<', $datePeriods['prev_end'])
                ->get()
                ->flatMap->purchaseOrderItems
                ->sum(fn($item) => $item->quantity * $item->at_cost);

            $changePercentage = $totalPrevious == 0
                ? 0
                : (($totalCurrent - $totalPrevious) / $totalPrevious) * 100;

            $data = [
                'Labels' => $labels,
                'Series' => [
                    [
                        'name' => 'Total At Cost',
                        'data' => $chartData,
                    ],
                ],
                'Total' => round($totalCurrent, 2),
                'ChangePercentage' => (int) round(min($changePercentage, 100)),
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "Something went wrong: {$th->getMessage()}");
        }
    }

    public function GetAppointmentCharData(DashboardFilterDTO $request): array
    {
        try {
            $periods = DashboardDateHelper::getChartPeriods($request);
            $datePeriods = DashboardDateHelper::getPeriods($request);

            $statuses = [
                AppointmentStatus::PENDING->value,
                AppointmentStatus::APPROVED->value,
                AppointmentStatus::RESCHEDULED->value,
                AppointmentStatus::CANCELLED->value,
                AppointmentStatus::COMPLETED->value,
                AppointmentStatus::NO_SHOW->value,
                AppointmentStatus::REASSIGN->value,
                AppointmentStatus::CHECKUP_DONE->value,
            ];

            $appointments = Appointment::select('status', 'created_at')
                ->where('created_at', '>=', $datePeriods['current_start'])
                ->where('created_at', '<', $datePeriods['current_end'])
                ->get();

            $series = [];
            foreach ($statuses as $status) {
                $series[$status] = [
                    'name' => $status, // frontend maps enum
                    'data' => array_fill(0, count($periods), 0),
                ];
            }

            foreach ($periods as $index => $period) {
                $start = $period['start'];
                $end   = $period['end'];

                $bucket = $appointments->filter(
                    fn($a) =>
                    $a->created_at >= $start &&
                        $a->created_at < $end
                );

                foreach ($bucket as $appointment) {
                    $status = $appointment->status;
                    if (isset($series[$status])) {
                        $series[$status]['data'][$index]++;
                    }
                }
            }

            $data = [
                'Labels' => array_column($periods, 'label'),
                'Series' => array_values($series),
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(
                500,
                "Something went wrong: {$th->getMessage()}"
            );
        }
    }

    public function generateClinicSummary(DashboardFilterDTO $request)
    {
        try {
            $datePeriods = DashboardDateHelper::getPeriods($request);
            $appointments = Appointment::select('status', 'created_at', 'reason')
                ->where('created_at', '>=', $datePeriods['current_start'])
                ->where('created_at', '<', $datePeriods['current_end'])
                ->get();

            $reasonCounts = $appointments->groupBy('reason')->map(function ($group) {
                return $group->count();
            })->mapWithKeys(function ($count, $reason) {
                return [AppointmentReasons::from($reason)->name => $count];
            });

            $dateStart = \Carbon\Carbon::parse($datePeriods['current_start'])->format('Y-m-d');
            $dateEnd = \Carbon\Carbon::parse($datePeriods['current_end'])->format('Y-m-d');

            $appointmentsData = [
                'dateRange' => $dateStart . ' to ' . $dateEnd, // only the dates
                'reason_counts' => $reasonCounts,
            ];

            // Step 2: Build prompt
            $prompt = "
                You are an AI analyzing school clinic appointments.

                Analyze the following appointment data and generate a structured summary. Focus on trends, common health issues, and actionable insights. Highlight percentages and patterns where possible.

                Appointments Data:
                " . json_encode($appointmentsData, JSON_PRETTY_PRINT) . "
                 Analyze the following appointment data and generate a structured summary. Focus on trends, common health issues, and actionable insights. Highlight percentages and patterns where possible.


                Generate output strictly in the following JSON format:

                {
                \"title\": string,           // A short descriptive title
                \"summary\": string,         // Overall summary of the appointment data
                \"insights\": [string],      // List of actionable or informative insights
                \"confidence\": number,      // AI confidence (0-100)
                \"generatedAt\": string      // ISO 8601 timestamp
                }

                Do NOT include any additional text, explanation, or formatting outside of this JSON object.
                ";

            // // Step 3: Send to Gemini
            $response = $this->geminiService->sendPrompt($prompt);

            $aiText = $response;

            if (!$aiText) {
                return ResponseHelper::errorResponse(400,  $response);
            }

            $aiData = json_decode($aiText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ResponseHelper::errorResponse(400, json_last_error_msg());
            }

            $aiId = 'ai-summary-' . time() . '-' . rand(1000, 9999);

            $aiSummary = AISummary::updateOrCreate([
                'ai_id' =>  $aiId,
                'title' => $aiData['title'],
                'summary' => $aiData['summary'],
                'insights' => $aiData['insights'],
                'confidence' => $aiData['confidence'],
            ]);

            return ResponseHelper::successWData(200, "Success",  $aiSummary);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(
                500,
                "Something went wrong: {$th->getMessage()}"
            );
        }
    }

    public function GetLatestAISummary()
    {
        try {
            $ai_summary = AISummary::latest('id')->first();

            if (!$ai_summary) {
                return ResponseHelper::errorResponse(404, "No AI summaries found");
            }

            return ResponseHelper::successWData(200, "Latest AI summary retrieved", $ai_summary);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(
                500,
                "Something went wrong: {$th->getMessage()}"
            );
        }
    }

    public function GetAppointmentReasonDistribution(DashboardFilterDTO $request)
    {
        try {
            $datePeriods = DashboardDateHelper::getPeriods($request);

            $current = Appointment::select('reason')
                ->where('created_at', '>=', $datePeriods['current_start'])
                ->where('created_at', '<', $datePeriods['current_end'])
                ->get()
                ->groupBy('reason')
                ->map(fn($g) => $g->count());

            $previous = Appointment::select('reason')
                ->where('created_at', '>=', $datePeriods['prev_start'])
                ->where('created_at', '<', $datePeriods['prev_end'])
                ->get()
                ->groupBy('reason')
                ->map(fn($g) => $g->count());

            $totalCurrent = max($current->sum(), 1);

            $reasonLabels = [
                AppointmentReasons::FEVER_OR_FLU_LIKE_SYMPTOMS->value => 'Fever or Flu-like Symptoms',
                AppointmentReasons::HEADACHE_OR_MIGRAINE->value => 'Headache or Migraine',
                AppointmentReasons::STOMACHACHE_OR_DIGESTIVE_PROBLEMS->value => 'Stomachache or Digestive Problems',
                AppointmentReasons::MINOR_INJURY_OR_ACCIDENT->value => 'Minor Injury or Accident',
                AppointmentReasons::ALLERGY_OR_ASTHMA_RELATED_SYMPTOMS->value => 'Allergy or Asthma-related Symptoms',
                AppointmentReasons::DENTAL_PAIN_OR_ORAL_HEALTH_CONCERNS->value => 'Dental Pain or Oral Health Concerns',
                AppointmentReasons::SKIN_CONDITIONS_OR_RASHES->value => 'Skin Conditions or Rashes',
                AppointmentReasons::FOLLOW_UP_CHECK_UP->value => 'Follow-up Check-up',
                AppointmentReasons::OTHER_HEALTH_CONCERNS->value => 'Other Health Concerns',
            ];

            $response = [];

            foreach ($reasonLabels as $reasonValue => $label) {
                $currentCount = $current[$reasonValue] ?? 0;
                $previousCount = $previous[$reasonValue] ?? 0;

                $percentage = round(($currentCount / $totalCurrent) * 100);

                if ($previousCount === 0 && $currentCount > 0) {
                    $trend = 'up';
                    $trendPercentage = 100;
                } elseif ($previousCount === 0) {
                    $trend = 'stable';
                    $trendPercentage = 0;
                } else {
                    $change = (($currentCount - $previousCount) / $previousCount) * 100;
                    $trendPercentage = round($change);

                    if ($change > 2) {
                        $trend = 'up';
                    } elseif ($change < -2) {
                        $trend = 'down';
                    } else {
                        $trend = 'stable';
                    }
                }

                $response[] = [
                    'reason' => $label,
                    'count' => $currentCount,
                    'percentage' => $percentage,
                    'trend' => $trend,
                    'trendPercentage' => $trendPercentage,
                ];
            }

            // Optional: sort by count descending (like dashboards usually do)
            usort($response, fn($a, $b) => $b['count'] <=> $a['count']);

            return ResponseHelper::successWData(200, 'Success', $response);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(
                500,
                "Something went wrong: {$th->getMessage()}"
            );
        }
    }


    public function GetAppointmentReasonsTrend(DashboardFilterDTO $request)
    {
        try {
            $periods = DashboardDateHelper::getChartPeriods($request);

            $reasonLabels = [
                AppointmentReasons::FEVER_OR_FLU_LIKE_SYMPTOMS->value => 'Fever/Flu',
                AppointmentReasons::HEADACHE_OR_MIGRAINE->value => 'Headache',
                AppointmentReasons::STOMACHACHE_OR_DIGESTIVE_PROBLEMS->value => 'Digestive',
                AppointmentReasons::MINOR_INJURY_OR_ACCIDENT->value => 'Injury',
                AppointmentReasons::ALLERGY_OR_ASTHMA_RELATED_SYMPTOMS->value => 'Allergy/Asthma',
                AppointmentReasons::FOLLOW_UP_CHECK_UP->value => 'Follow-up',
                AppointmentReasons::OTHER_HEALTH_CONCERNS->value => 'Other',
            ];

            $result = [];

            foreach ($periods as $period) {
                $start = $period['start'];
                $end = $period['end'];
                $label = $period['label'];

                // Fetch counts for this period
                $appointments = Appointment::select('reason')
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<', $end)
                    ->get()
                    ->groupBy('reason')
                    ->map(fn($g) => $g->count());

                // Build reasons object
                $reasons = [];
                foreach ($reasonLabels as $reasonValue => $reasonName) {
                    $reasons[$reasonName] = $appointments[$reasonValue] ?? 0;
                }

                $result[] = [
                    'date' => $label,
                    'reasons' => $reasons,
                ];
            }

            // Step 2: Build AI prompt
            $prompt = "
            You are an AI analyzing school clinic appointment trends.

            Analyze the following appointment data and generate a **single, cohesive AI Insight paragraph**. 
            Focus on:

            - Key trends over the past 4 weeks
            - Most common reasons for visits
            - Whether visits are increasing, decreasing, or stable
            - Actionable suggestions or recommendations if patterns emerge

            Appointment Trend Data:
            " . json_encode($result, JSON_PRETTY_PRINT) . "

            Do NOT include JSON, lists, extra commentary, or any explanation outside this paragraph.
            Keep it clear, concise, and actionable.
            ";


            // Send prompt to Gemini AI
            $aiResponse = $this->geminiService->sendPrompt($prompt);
            $aiInsight = $aiResponse ?? "No AI insight available";

            return ResponseHelper::successWData(200, 'Success', [
                'result' => $result,
                'aiInsight' => $aiInsight,
            ]);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(
                500,
                "Something went wrong: {$th->getMessage()}"
            );
        }
    }
}
