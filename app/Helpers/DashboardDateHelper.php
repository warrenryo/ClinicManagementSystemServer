<?php

namespace App\Helpers;

use App\DTO\Response\DashboardFilterDTO;
use App\Enums\FilterTimeIntervals;
use Carbon\Carbon;

class DashboardDateHelper
{
    public static function getPeriods(DashboardFilterDTO $request): array
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();

        if ($request->startDate && $request->endDate) {
            $currentStart = Carbon::parse($request->startDate)->startOfDay();
            $currentEnd   = Carbon::parse($request->endDate)->addDay()->startOfDay();

            $rangeDays = $currentStart->diffInDays($currentEnd);
            $prevStart = $currentStart->copy()->subDays($rangeDays);
            $prevEnd   = $currentStart->copy();
        } elseif ($request->dateTime) {
            $currentStart = Carbon::parse($request->dateTime)->startOfDay();
            $currentEnd   = $currentStart->copy()->addDay();
            $prevStart    = $currentStart->copy()->subDay();
            $prevEnd      = $currentStart->copy();
        } else {
            switch ($request->filterTimeIntervals) {
                case FilterTimeIntervals::WEEKLY:
                    $currentStart = $today->copy()->startOfWeek();
                    $currentEnd   = $today->copy()->endOfWeek()->addDay();
                    $prevStart    = $currentStart->copy()->subWeek();
                    $prevEnd      = $currentStart->copy();
                    break;

                case FilterTimeIntervals::MONTHLY:
                    $currentStart = $today->copy()->startOfMonth();
                    $currentEnd   = $today->copy()->endOfMonth()->addDay();
                    $prevStart    = $currentStart->copy()->subMonth();
                    $prevEnd      = $currentStart->copy();
                    break;

                case FilterTimeIntervals::YEARLY:
                    $currentStart = $today->copy()->startOfYear();
                    $currentEnd   = $today->copy()->endOfYear()->addDay();
                    $prevStart    = $currentStart->copy()->subYear();
                    $prevEnd      = $currentStart->copy();
                    break;

                case FilterTimeIntervals::DAILY:
                default:
                    $currentStart = $today->copy();
                    $currentEnd   = $today->copy()->addDay();
                    $prevStart    = $today->copy()->subDay();
                    $prevEnd      = $today->copy();
                    break;
            }
        }

        return [
            'current_start' => $currentStart,
            'current_end'   => $currentEnd,
            'prev_start'    => $prevStart,
            'prev_end'      => $prevEnd,
        ];
    }

    public static function getChartPeriods(DashboardFilterDTO $request): array
    {
        $periods = [];
        $datePeriods = self::getPeriods($request);
        $today = Carbon::now('Asia/Manila')->startOfDay();

        if ($request->startDate && $request->endDate) {
            $totalDays = $datePeriods['current_start']
                ->diffInDays($datePeriods['current_end']);

            for ($i = 0; $i < $totalDays; $i++) {
                $start = $datePeriods['current_start']->copy()->addDays($i);
                $periods[] = [
                    'start' => $start,
                    'end'   => $start->copy()->addDay(),
                    'label' => $start->format('M d'),
                ];
            }
        } elseif ($request->dateTime || $request->filterTimeIntervals === FilterTimeIntervals::DAILY) {
            $start = $request->dateTime
                ? Carbon::parse($request->dateTime)->startOfDay()
                : $today;

            for ($hour = 0; $hour < 24; $hour++) {
                $periods[] = [
                    'start' => $start->copy()->addHours($hour),
                    'end'   => $start->copy()->addHours($hour + 1),
                    'label' => $start->copy()->addHours($hour)->format('H:i'),
                ];
            }
        } else {
            switch ($request->filterTimeIntervals) {
                case FilterTimeIntervals::WEEKLY:
                    $startOfWeek = $today->copy()->startOfWeek();
                    for ($i = 0; $i < 7; $i++) {
                        $day = $startOfWeek->copy()->addDays($i);
                        $periods[] = [
                            'start' => $day,
                            'end'   => $day->copy()->addDay(),
                            'label' => $day->format('D'),
                        ];
                    }
                    break;

                case FilterTimeIntervals::MONTHLY:
                    $firstDay = $today->copy()->startOfMonth();
                    $lastDay  = $firstDay->copy()->addMonth();

                    $weeks = ceil($firstDay->diffInDays($lastDay) / 7);
                    for ($week = 0; $week < $weeks; $week++) {
                        $start = $firstDay->copy()->addDays($week * 7);
                        $end   = $start->copy()->addDays(7);
                        if ($end->gt($lastDay)) $end = $lastDay;

                        $periods[] = [
                            'start' => $start,
                            'end'   => $end,
                            'label' => 'Week ' . ($week + 1),
                        ];
                    }
                    break;

                case FilterTimeIntervals::YEARLY:
                    for ($month = 1; $month <= 12; $month++) {
                        $start = Carbon::create($today->year, $month, 1);
                        $periods[] = [
                            'start' => $start,
                            'end'   => $start->copy()->addMonth(),
                            'label' => $start->format('M'),
                        ];
                    }
                    break;
            }
        }

        return $periods;
    }
}
