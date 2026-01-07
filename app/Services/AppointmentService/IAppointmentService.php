<?php

namespace App\Services\AppointmentService;

use Illuminate\Http\Request;

interface IAppointmentService
{
    public function CreateAppointment(Request $request);
    public function GetAppointmentDates(Request $request);
}
