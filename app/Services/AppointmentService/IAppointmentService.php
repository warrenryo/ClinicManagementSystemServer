<?php

namespace App\Services\AppointmentService;

use App\DTO\Response\GetPaginatedDTO;
use Illuminate\Http\Request;

interface IAppointmentService
{
    public function CreateAppointment(Request $request);
    public function GetAppointmentDates(Request $request);
    public function GetUserAppointments(GetPaginatedDTO $request);
    public function GetUserAppointmentDetails($appointmentId);
    public function GetOverallAppointments(GetPaginatedDTO $request);
    public function GetAppointmentsByDate($date);
    public function GetAppointmentCalendarCounts($month, $year);
    public function SetAppointmentStatus($appointmentId, $status);
    public function RescheduleAppointment(Request $request, $appointmentId);
    public function AssignDoctorToAppointment(Request $request);
}
