<?php

namespace App\Services\MedicalRecordService;

use App\DTO\Response\GetPaginatedDTO;
use App\DTO\Response\PaginatedTableResponse;
use App\Enums\ActionTaken;
use App\Enums\AppointmentReasons;
use App\Enums\AppointmentStatus;
use App\Enums\ApprovalStatus;
use App\Enums\Course;
use App\Enums\UOM;
use App\Enums\UserRoles;
use App\Enums\YearLevel;
use App\Helpers\UserHelper;
use App\Models\Auth\DoctorDetails;
use App\Models\Auth\User;
use App\Models\Auth\UserDetails;
use App\Models\Inventory\Products;
use App\Models\Medical\MedicalCertificate;
use App\Models\Medical\MedicalRecords;
use App\Models\Medical\RequestMedicalRecords;
use App\Models\Scheduling\Appointment;
use App\Response\ResponseHelper;
use App\Services\GeminiService\IGeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class MedicalRecordService implements IMedicalRecordService
{
    /**
     * Create a new class instance.
     */
    protected $geminiService;

    public function __construct(IGeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function AddVitalSign(Request $request, int $appointmentId)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();

            $validatedData = $request->validate([
                'Temperature' => 'required|numeric',
                'BloodPressure' => 'required|string',
                'PulseRate' => 'required|integer',
                'Height' => 'required|numeric',
                'Weight' => 'required|numeric'
            ]);

            $appointment = Appointment::findOrFail($appointmentId);

            $appointment->medicalRecords()->create([
                'user_details_id' => $userDetailsId,
                'blood_pressure' => $validatedData['BloodPressure'],
                'pulse_rate' => $validatedData['PulseRate'],
                'temperature' => $validatedData['Temperature'],
                'height' => $validatedData['Height'],
                'weight' => $validatedData['Weight'],
            ]);

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function GetAppointmentMedical($appointmentId)
    {
        try {
            $appointment = Appointment::findOrFail($appointmentId);
            $record = $appointment->medicalRecords()->first();

            $hasUserDetails = $appointment->userDetails()->exists();
            $hasStudentDetails = $hasUserDetails
                && $appointment->userDetails->studentDetails()->exists();

            $initial_details = [
                'RecordId' => $record->id ?? null,

                'UserDetailsId' => $hasUserDetails
                    ? $appointment->userDetails->id
                    : null,
                'WalkinId' => $appointment->walkin_id ? $appointment->walkin->id : null,
                'Name' => $hasUserDetails
                    ? $appointment->userDetails->first_name . ' ' . $appointment->userDetails->last_name
                    : ($appointment->walkin
                        ? $appointment->walkin->first_name . ' ' . $appointment->walkin->last_name
                        : null
                    ),

                'StudentDetails' => $hasStudentDetails
                    ? [
                        'Course' => $appointment->userDetails->studentDetails->course,
                        'Year'   => $appointment->userDetails->studentDetails->year_level,
                    ]
                    : null,

                'TeacherDetails' => null,
                'StaffDetails'   => null,

                'VisitDate' => $appointment->appointment_date,
                'VisitTime' => $appointment->appointment_time,
                'Reason'    => $appointment->reason,

                'InitialVitalSign' => $record
                    ? [
                        'AppointmentId' => $appointment->id,
                        'Temperature'   => $record->temperature,
                        'BloodPressure' => $record->blood_pressure,
                        'PulseRate'     => $record->pulse_rate,
                        'Height'        => $record->height,
                        'Weight'        => $record->weight,
                    ]
                    : null,
            ];

            return ResponseHelper::successWData(200, "Success", $initial_details);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, "{$th->getMessage()}");
        }
    }

    public function CreateMedicalRecord(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'RecordId' => 'nullable|integer',
                'UserDetailsId' => 'nullable|integer',
                'WalkinId' => 'nullable|integer',
                'Symptoms' => 'nullable|string',

                'VitalSigns' => 'nullable|array',
                'VitalSigns.AppointmentId' => 'nullable|integer',
                'VitalSigns.Temperature' => 'nullable|numeric',
                'VitalSigns.BloodPressure' => 'nullable|string',
                'VitalSigns.PulseRate' => 'nullable|integer',
                'VitalSigns.Height' => 'nullable|numeric',
                'VitalSigns.Weight' => 'nullable|numeric',

                'Findings' => 'nullable|string',
                'Remarks' => 'nullable|string',

                'ActionTaken' => 'nullable|array',
                'ActionTaken.*' => 'integer',

                'ItemsProvided' => 'nullable|array',
                'ItemsProvided.*.Product' => 'required|array',
                'ItemsProvided.*.Product.Id' => 'required|integer|exists:products,id',
                'ItemsProvided.*.Quantity' => 'required|integer|min:1',
                'ItemsProvided.*.Notes' => 'nullable|string',
            ]);

            DB::transaction(function () use ($validatedData, &$medical_record) {

                $userDetailsId = UserHelper::getUserDetailsId();

                $medical_record = isset($validatedData['RecordId'])
                    ? MedicalRecords::find($validatedData['RecordId'])
                    : null;

                $doctor = DoctorDetails::where('user_details_id', $userDetailsId)->first();

                if (!$doctor)
                    return ResponseHelper::errorResponse(404, "Doctor not found");

                $payload = [
                    'appointment_id'   => data_get($validatedData, 'VitalSigns.AppointmentId'),
                    'user_details_id'  => $validatedData['UserDetailsId'],
                    'walkin_id'        => $validatedData['WalkinId'],
                    'reference_no'     => Str::upper(Str::random(10)),
                    'doctor_id'        =>  $doctor->id,
                    'temperature'      => data_get($validatedData, 'VitalSigns.Temperature'),
                    'blood_pressure'   => data_get($validatedData, 'VitalSigns.BloodPressure'),
                    'pulse_rate'       => data_get($validatedData, 'VitalSigns.PulseRate'),
                    'height'           => data_get($validatedData, 'VitalSigns.Height'),
                    'weight'           => data_get($validatedData, 'VitalSigns.Weight'),
                    'symptoms'         => $validatedData['Symptoms'] ?? null,
                    'action_taken'     => $validatedData['ActionTaken'] ?? [],
                    'remarks'          => $validatedData['Remarks'] ?? null,
                    'findings'         => $validatedData['Findings'] ?? null,
                    'is_done'          => true
                ];

                if ($medical_record) {
                    $medical_record->update($payload);

                    $medical_record->medicalItems()->delete();
                } else {
                    $medical_record = MedicalRecords::create($payload);
                }

                if (!empty($validatedData['ItemsProvided'])) {
                    foreach ($validatedData['ItemsProvided'] as $item) {
                        $medical_record->medicalItems()->create([
                            'products_id' => $item['Product']['Id'],
                            'quantity'   => $item['Quantity'],
                            'notes'      => $item['Notes'] ?? null,
                        ]);

                        $product = Products::find($item['Product']['Id']);
                        if ($product) {
                            $product->quantity -= $item['Quantity'];
                            $product->save();
                        }
                    }
                }

                $appointment = $medical_record->appointment;
                if ($appointment) {
                    $appointment->status = AppointmentStatus::CHECKUP_DONE->value;
                    $appointment->save();
                }
            });

            return ResponseHelper::successResponse(200, "Success");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetAllMedicalRecordsPaginated(GetPaginatedDTO $request)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();

            $user = User::whereHas('userDetails', function ($q) use ($userDetailsId) {
                $q->where('id', $userDetailsId);
            })->firstOrFail();


            $query = MedicalRecords::query();

            if ($user->role === UserRoles::STUDENTS) {
                $query->where('user_details_id', $userDetailsId);
            }

            $count = $query->count();

            $records = $query
                ->orderBy('id', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $paginated = $records->map(function ($rec) {
                return [
                    'Id' => $rec->id,
                    'FullName' => $rec->userDetails()->exists()
                        ? $rec->userDetails->first_name . ' ' . $rec->userDetails->last_name
                        : $rec->walkin->first_name . ' ' . $rec->walkin->last_name,
                    'CreatedBy' => $rec->doctorDetails->userDetails->first_name . ' ' .  $rec->doctorDetails->userDetails->last_name,
                    'ReferenceNo' => $rec->reference_no,
                    'IsRequested' => $rec->requestMedRecord()->exists(),
                    'CreatedAt' => $rec->created_at
                ];
            })->toArray();

            $result = new PaginatedTableResponse($paginated, $count);

            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function ViewMedicalRecordData($medId)
    {
        try {
            $record = MedicalRecords::with([
                'userDetails.studentDetails',
                'appointment',
                'medicalItems.product'
            ])->findOrFail($medId);

            $data = [
                'RecordId'       => $record->id,
                'ReferenceNo'    => $record->reference_no,
                'UserDetailsId'  => $record->user_details_id,

                'PatientName'    => $record->userDetails
                    ? $record->userDetails->first_name . ' ' . $record->userDetails->last_name
                    : ($record->walkin
                        ? $record->walkin->first_name . ' ' . $record->walkin->last_name
                        : null
                    ),

                'StudentDetails' => $record->userDetails && $record->userDetails->studentDetails
                    ? [
                        'Course' => $record->userDetails->studentDetails->course,
                        'Year'   => $record->userDetails->studentDetails->year_level,
                    ]
                    : null,

                'VisitDate'      => $record->appointment?->appointment_date,
                'VisitTime'      => $record->appointment?->appointment_time,
                'Reason'         => $record->appointment?->reason,

                'Symptoms'       => $record->symptoms,

                'VitalSigns'     => [
                    'AppointmentId' => $record->appointment?->id,
                    'Temperature'   => $record->temperature,
                    'BloodPressure' => $record->blood_pressure,
                    'PulseRate'     => $record->pulse_rate,
                    'Height'        => $record->height,
                    'Weight'        => $record->weight,
                ],

                'Findings'       => $record->findings,
                'ActionTaken'    => $record->action_taken,
                'Remarks'        => $record->remarks,

                'ItemsProvided'  => $record->medicalItems->map(function ($item) {
                    return [
                        'Product' => [
                            'Id'           => $item->product?->id,
                            'Title'        => $item->product?->title,
                            'UOM'          => $item->product?->uom,
                            'Quantity'     => $item->product?->quantity,
                            'PackagingQty' => $item->product?->packaging_qty,
                        ],
                        'Quantity' => $item->quantity,
                        'Notes'    => $item->notes,
                    ];
                }),

                'DoctorName'       => $record->doctorDetails && $record->doctorDetails->userDetails
                    ? $record->doctorDetails->userDetails->first_name . ' ' . $record->doctorDetails->userDetails->last_name
                    : null,

                'DoctorSignature' => '',

                'CreatedAt'        => $record->created_at,
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function exportMedicalRecordPdf($medId)
    {
        try {
            $record = MedicalRecords::with([
                'userDetails.studentDetails',
                'appointment',
                'medicalItems.product',
                'doctorDetails.userDetails',
            ])->findOrFail($medId);

            $data = [
                'RecordId'     => $record->id,
                'ReferenceNo'  => $record->reference_no,
                'PatientName'  => $record->userDetails->first_name
                    . ' ' . $record->userDetails->last_name,

                'StudentDetails' => $record->userDetails->studentDetails ? [
                    'Course' => Course::from($record->userDetails->studentDetails->course)->label(),
                    'Year'   => YearLevel::from($record->userDetails->studentDetails->year_level)->label(),
                ] : null,

                'VisitDate' => $record->appointment->appointment_date,
                'VisitTime' => $record->appointment->appointment_time,
                'Reason'    => AppointmentReasons::from($record->appointment->reason)->label(),

                'Symptoms' => $record->symptoms,

                'VitalSigns' => [
                    'Temperature'   => $record->temperature,
                    'BloodPressure' => $record->blood_pressure,
                    'PulseRate'     => $record->pulse_rate,
                    'Height'        => $record->height,
                    'Weight'        => $record->weight,
                ],

                'Findings'    => $record->findings,
                'ActionTaken' =>  collect($record->action_taken)
                    ->map(fn($action) => ActionTaken::from($action)->label())
                    ->toArray(),
                'Remarks'     => $record->remarks,

                'ItemsProvided' => $record->medicalItems->map(function ($item) {
                    return [
                        'Title'    => $item->product->title ?? '',
                        'UOM'      => UOM::from($item->product->uom)->label() ?? '',
                        'Quantity' => $item->quantity ?? '',
                        'Notes'    => $item->notes ?? '',
                    ];
                })->toArray(),

                'DoctorName' => $record->doctorDetails->userDetails->first_name
                    . ' ' . $record->doctorDetails->userDetails->last_name,
                'CreatedAt'  => $record->created_at,
            ];

            $pdf = PDF::setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                ])
                ->loadView('pdf.medical_records', $data);

            $filename = 'Medical_Record_' . $data['ReferenceNo'] . '.pdf';

            $pdfContent = base64_encode($pdf->output());

            return ResponseHelper::successWData(200, 'Success', [
                'filename' => $filename,
                'file' => $pdfContent,
            ]);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function RequestMedicalCertificate($medical_record_id)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();

            $existing = RequestMedicalRecords::where('medical_records_id', $medical_record_id)->first();

            if ($existing) {
                return ResponseHelper::errorResponse(400, "You have already requested a request");
            } else {
                RequestMedicalRecords::create([
                    'user_details_id' => $userDetailsId,
                    'medical_records_id' => $medical_record_id
                ]);
            }

            return ResponseHelper::successResponse(200, "Your request has been submitted");
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function RequestCertificatePaginated(GetPaginatedDTO $request)
    {
        try {
            $userDetailsId = UserHelper::getUserDetailsId();

            $user_details = UserDetails::findOrFail($userDetailsId);

            $query = RequestMedicalRecords::query();

            if ($user_details->user->role === UserRoles::DOCTORS) {
                $query->whereHas('medicalRecords.doctorDetails', function ($q) use ($userDetailsId) {
                    $q->where('user_details_id', $userDetailsId);
                });
            } elseif ($user_details->user->role === UserRoles::STUDENTS) {

                $query->where('user_details_id', $userDetailsId);
            }

            $count = $query->count();

            $records = $query
                ->orderBy('id', 'desc')
                ->skip($request->Skip)
                ->take($request->Take)
                ->get();

            $paginated = $records->map(function ($rec) {
                return [
                    'Id' => $rec->id,
                    'MedicalRecordId' => $rec->medicalRecords->id,
                    'FullName' =>  $rec->userDetails->first_name . ' ' . $rec->userDetails->last_name,
                    'ReferenceNo' => $rec->medicalRecords->reference_no ?? null,
                    'IsDone' => (bool)$rec->is_done,
                    'CreatedAt' => $rec->created_at
                ];
            })->toArray();

            $result = new PaginatedTableResponse($paginated, $count);

            return ResponseHelper::successWData(200, "Success", $result);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetRequestCertFormDetails($reqId)
    {
        try {
            $req_med = RequestMedicalRecords::findOrFail($reqId);

            $data = [
                'medicalRecordId' => $req_med->medicalRecords->id,
                'patientName' => $req_med->userDetails->first_name . ' ' . $req_med->userDetails->last_name,
                'dateOfBirth' => Carbon::parse($req_med->userDetails->birth_date) ?? null,
                'dateIssued' => $req_med->created_at,
                'doctorName' => $req_med->medicalRecords->doctorDetails->userDetails->first_name . ' ' . $req_med->medicalRecords->doctorDetails->userDetails->last_name,
                'doctorLicenseNo' => $req_med->medicalRecords->doctorDetails->license_number
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function AIAssistedCertificate($medId)
    {
        try {
            $med_rec = MedicalRecords::findOrFail($medId);
            $med_data = [
                'temperature' => $med_rec->temperature,
                'bloodPressure' => $med_rec->blood_pressure,
                'pulseRate' => $med_rec->pulse_rate,
                'symptoms' => $med_rec->symptoms,
                'findings' => $med_rec->findings,
                'remarks' => $med_rec->remarks,
            ];

            $prompt = "
                You are a medical AI assistant helping to draft a medical certificate based on clinical examination data from a school clinic appointment.

                CLINICAL DATA:
                " . json_encode($med_data, JSON_PRETTY_PRINT) . "

                TASK:
                Based on the clinical data provided, generate professional medical certificate recommendations. Use clinical judgment to:
                1. Identify the most likely diagnosis based on symptoms, vital signs, and findings
                2. Formulate appropriate chief complaints
                3. Document physical examination findings
                4. Provide evidence-based treatment recommendations
                5. Determine fitness for work/study
                6. Assess need for follow-up care
                7. Recommend any activity restrictions

                IMPORTANT GUIDELINES:
                - Be professional and use appropriate medical terminology
                - Base diagnosis on the symptoms and findings provided
                - Number of rest days should align with the severity of the condition
                - If vital signs are abnormal, address them in the diagnosis and recommendations
                - Restrictions should be specific and relevant to the diagnosis
                - Follow-up should be recommended for conditions requiring monitoring
                - Fit to work should be 'false' only if the condition requires rest or isolation

                REQUIRED OUTPUT FORMAT (JSON ONLY):
                Return ONLY a valid JSON object with NO additional text, explanations, or markdown formatting:

                {
                \"diagnosis\": \"Primary diagnosis based on symptoms and findings\",
                \"chiefComplaint\": \"Main complaint or presenting symptom(s)\",
                \"physicalExamination\": \"Documented physical examination findings including vital signs\",
                \"recommendations\": \"Treatment plan and medical advice (medications, rest, hydration, etc.)\",
                \"numberOfDays\": number,
                \"fitToWork\": boolean,
                \"needsFollowUp\": boolean,
                \"restrictions\": \"Specific activity restrictions or precautions\",
                \"remarks\": \"Additional clinical notes or observations\"
                }

                EXAMPLE OUTPUT STRUCTURE:
                {
                \"diagnosis\": \"Acute Upper Respiratory Tract Infection\",
                \"chiefComplaint\": \"Cough, fever, and sore throat for 2 days\",
                \"physicalExamination\": \"Temperature: 38.5°C, BP: 120/80 mmHg, Pulse: 88 bpm. Pharynx erythematous with tonsillar enlargement. Clear breath sounds bilaterally. No lymphadenopathy.\",
                \"recommendations\": \"Complete bed rest, adequate hydration (8-10 glasses of water daily), Paracetamol 500mg every 6 hours for fever, Vitamin C 500mg once daily. Avoid cold beverages and strenuous activities.\",
                \"numberOfDays\": 3,
                \"fitToWork\": false,
                \"needsFollowUp\": true,
                \"restrictions\": \"Avoid strenuous physical activities, sports, and prolonged sun exposure. Maintain proper hand hygiene to prevent transmission.\",
                \"remarks\": \"Patient advised to return if symptoms worsen or persist beyond 5 days, or if high-grade fever (>39°C) develops.\"
                }

                Generate the medical certificate data now:
                    ";

            // Send to Gemini
            $response = $this->geminiService->sendPrompt($prompt);

            // Parse and validate the JSON response
            $jsonResponse = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ResponseHelper::errorResponse(500, 'Invalid AI response format');
            }

            $requiredFields = [
                'diagnosis',
                'chiefComplaint',
                'physicalExamination',
                'recommendations',
                'numberOfDays',
                'fitToWork',
                'needsFollowUp',
                'restrictions',
                'remarks'
            ];

            foreach ($requiredFields as $field) {
                if (!isset($jsonResponse[$field])) {
                    return ResponseHelper::errorResponse(500, "Missing required field: {$field}");
                }
            }

            return ResponseHelper::successWData(200, "Success", $jsonResponse);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function CreateMedicalCertificate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'medicalRecordId' => 'required|integer|exists:medical_records,id',
                'dateIssued' => 'required|date',
                'diagnosis' => 'required|string',
                'chiefComplaint' => 'required|string',
                'physicalExamination' => 'required|string',
                'recommendations' => 'required|string',
                'restPeriodFrom' => 'nullable|date',
                'restPeriodTo' => 'nullable|date|after_or_equal:restPeriodFrom',
                'numberOfDays' => 'required|integer|min:0',
                'fitToWork' => 'required|boolean',
                'needsFollowUp' => 'required|boolean',
                'followUpDate' => 'nullable|date|after:today',
                'restrictions' => 'nullable|string',
                'remarks' => 'nullable|string',
                'doctorSignature' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::errorResponse(422, 'Validation failed', $validator->errors());
            }

            $medicalRecord = MedicalRecords::find($request->medicalRecordId);
            if (!$medicalRecord) {
                return ResponseHelper::errorResponse(404, 'Medical record not found');
            }

            $certificateData = [
                'medical_records_id' => $request->medicalRecordId,
                'date_issued' => Carbon::parse($request->dateIssued)->format('Y-m-d'),
                'diagnosis' => $request->diagnosis,
                'chief_complaint' => $request->chiefComplaint,
                'physical_examination' => $request->physicalExamination,
                'recommendations' => $request->recommendations,
                'rest_period_from' => $request->restPeriodFrom ? Carbon::parse($request->restPeriodFrom)->format('Y-m-d') : null,
                'rest_period_to' => $request->restPeriodTo ? Carbon::parse($request->restPeriodTo)->format('Y-m-d') : null,
                'number_of_days' => $request->numberOfDays,
                'fit_to_work' => $request->fitToWork,
                'needs_follow_up' => $request->needsFollowUp,
                'follow_up_date' => $request->followUpDate ? Carbon::parse($request->followUpDate)->format('Y-m-d') : null,
                'restrictions' => $request->restrictions,
                'remarks' => $request->remarks,
                'doctor_signature' => $request->doctorSignature,
            ];

            $createdCert = MedicalCertificate::create($certificateData);

            $req = RequestMedicalRecords::where('medical_records_id', $request->medicalRecordId)->first();

            $req->is_done = true;
            $req->save();

            return ResponseHelper::successWData(200, "Success",   $createdCert->id);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function ViewMedicalCertificate($medicalRecId)
    {
        try {
            $cert = MedicalCertificate::where('medical_records_id', $medicalRecId)
                ->first();

            if (!$cert) {
                return ResponseHelper::errorResponse(404, 'Medical certificate not found');
            }

            $medicalRecord = $cert->medicalRecords;
            $patient = $medicalRecord->userDetails ?? null;

            $doctor = $medicalRecord->doctorDetails;
            $data = [
                'medicalRecordId' => $cert->medical_records_id,
                'patientName' => $patient ? $patient->first_name . ' ' . $patient->last_name : '',
                'dateOfBirth' => $patient && $patient->birth_date
                    ? Carbon::parse($patient->birth_date)->format('Y-m-d')
                    : null,
                'dateIssued' => $cert->date_issued
                    ? Carbon::parse($cert->date_issued)->format('Y-m-d')
                    : Carbon::now()->format('Y-m-d'),
                'diagnosis' => $cert->diagnosis ?? '',
                'chiefComplaint' => $cert->chief_complaint ?? '',
                'physicalExamination' => $cert->physical_examination ?? '',
                'recommendations' => $cert->recommendations ?? '',
                'restPeriodFrom' => $cert->rest_period_from
                    ? Carbon::parse($cert->rest_period_from)->format('Y-m-d')
                    : null,
                'restPeriodTo' => $cert->rest_period_to
                    ? Carbon::parse($cert->rest_period_to)->format('Y-m-d')
                    : null,
                'numberOfDays' => $cert->number_of_days ?? 0,
                'fitToWork' => (bool) $cert->fit_to_work,
                'needsFollowUp' => (bool) $cert->needs_follow_up,
                'followUpDate' => $cert->follow_up_date
                    ? Carbon::parse($cert->follow_up_date)->format('Y-m-d')
                    : null,
                'restrictions' => $cert->restrictions ?? '',
                'remarks' => $cert->remarks ?? '',
                'doctorName' => $doctor && $doctor->userDetails
                    ? $doctor->userDetails->first_name . ' ' . $doctor->userDetails->last_name
                    : '',
                'doctorLicenseNo' => $doctor ? $doctor->license_number : '',
                'doctorSignature' => $cert->doctor_signature ?? null,
            ];

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }

    public function GetMedicalCertificates($userDetailsId)
    {
        try {
            $cert = MedicalCertificate::whereHas('medicalRecords', function ($q) use ($userDetailsId) {
                $q->where('user_details_id', $userDetailsId);
            })->get();

            $data = $cert->map(function ($certificate) {
                return [
                    'certificateId' => $certificate->id,
                    'referenceNo' =>  $certificate->medicalRecords->reference_no,
                    'issueDate' =>  Carbon::parse($certificate->date_issued),
                    'validUntil' => $certificate->rest_period_to
                        ? Carbon::parse($certificate->rest_period_to)
                        : Carbon::now()->addDays(10),
                    'purpose' => "Medical Certificate",
                    'doctor' => $certificate->medicalRecords->doctorDetails->userDetails->first_name . ' ' . $certificate->medicalRecords->doctorDetails->userDetails->last_name,
                    'diagnosis' => $certificate->diagnosis,
                    'recommendations' => $certificate->recommendations,
                ];
            })->toArray();

            return ResponseHelper::successWData(200, "Success", $data);
        } catch (\Throwable $th) {
            return ResponseHelper::errorResponse(500, $th->getMessage());
        }
    }
}
