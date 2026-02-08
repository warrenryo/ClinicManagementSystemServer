{{-- resources/views/pdf/medical_record.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 40px 44px 50px 44px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10.5px;
            color: #2c3e50;
            line-height: 1.5;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            border-bottom: 2.5px solid #0369a1;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header h1 {
            font-size: 19px;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 3px;
        }

        .header .subtitle {
            font-size: 9px;
            color: #7f8c8d;
            font-style: italic;
        }

        /* ── Meta bar ── */
        .meta-bar {
            background: #e0f2fe;
            border-radius: 3px;
            padding: 7px 12px;
            margin-bottom: 16px;
            font-size: 9.5px;
        }

        .meta-bar table {
            width: 100%;
        }

        .meta-bar td {
            padding: 1px 0;
        }

        .meta-bar .label {
            color: #5d6d7e;
            font-weight: bold;
            width: 110px;
        }

        .meta-bar .value {
            color: #0369a1;
        }

        /* ── Section header ── */
        .section {
            margin-bottom: 15px;
        }

        .section-head {
            background: #0369a1;
            color: #fff;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 4.5px 10px;
            border-radius: 3px;
            margin-bottom: 7px;
        }

        /* ── Two-col info grid ── */
        .row {
            width: 100%;
            margin-bottom: 5px;
        }

        .row td {
            vertical-align: top;
            width: 50%;
            padding-right: 18px;
        }

        .row td:last-child {
            padding-right: 0;
        }

        .field-label {
            font-size: 8.5px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 1px;
        }

        .field-value {
            font-size: 10.5px;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 1px dotted #bdc3c7;
            min-height: 17px;
            padding-bottom: 2px;
        }

        /* ── Full-width field ── */
        .full-field {
            margin-bottom: 8px;
        }

        .full-field .field-label {
            font-size: 8.5px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .full-field .field-value {
            font-size: 10.5px;
            color: #2c3e50;
            line-height: 1.6;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px;
            background: #f9fafb;
            min-height: 50px;
        }

        /* ── Vital signs grid ── */
        .vitals-grid {
            display: table;
            width: 100%;
            margin-top: 5px;
        }

        .vital-item {
            display: table-cell;
            width: 20%;
            padding: 6px;
            text-align: center;
            background: #f4f6f7;
            border: 1px solid #e5e7eb;
        }

        .vital-label {
            font-size: 7.5px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .vital-value {
            font-size: 13px;
            color: #0369a1;
            font-weight: 700;
        }

        /* ── Action tags ── */
        .action-tags {
            margin-top: 3px;
        }

        .action-tag {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 8.5px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 10px;
            margin-right: 5px;
            margin-bottom: 4px;
        }

        /* ── Items table ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        .items-table thead th {
            background: #d1e9f6;
            color: #0369a1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: left;
            padding: 5px 8px;
            border-bottom: 1.5px solid #0369a1;
        }

        .items-table tbody td {
            font-size: 10px;
            padding: 6px 8px;
            border-bottom: 1px solid #eaecee;
            vertical-align: top;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .items-table .empty-msg {
            text-align: center;
            color: #95a5a6;
            font-style: italic;
            padding: 9px 0;
        }

        .items-table .notes {
            font-size: 9px;
            color: #5d6d7e;
            font-style: italic;
        }

        /* ── Signature ── */
        .signature-wrap {
            margin-top: 34px;
            text-align: right;
        }

        .signature-box {
            display: inline-block;
            width: 200px;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #2c3e50;
            height: 38px;
            width: 100%;
            margin-bottom: 4px;
        }

        .sig-name {
            font-size: 10px;
            color: #0369a1;
            font-weight: 600;
        }

        .sig-label {
            font-size: 8.5px;
            color: #7f8c8d;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 18px;
            left: 44px;
            right: 44px;
            border-top: 1px solid #bdc3c7;
            padding-top: 6px;
            font-size: 8.5px;
            color: #95a5a6;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>School Health Services</h1>
        <div class="subtitle">Clinic Management System &mdash; Medical Record</div>
    </div>

    <!-- Meta bar -->
    <div class="meta-bar">
        <table>
            <tr>
                <td class="label">Reference No.</td>
                <td class="value">{{ $ReferenceNo ?? '—' }}</td>
                <td style="width:40px"></td>
                <td class="label" style="width:90px">Record ID</td>
                <td class="value">{{ $RecordId ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Visit Date</td>
                <td class="value">
                    @if(isset($VisitDate) && $VisitDate)
                    {{ \Carbon\Carbon::parse($VisitDate)->format('F d, Y') }}
                    @else
                    —
                    @endif
                </td>
                <td style="width:40px"></td>
                <td class="label" style="width:90px">Visit Time</td>
                <td class="value">{{ $VisitTime ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Generated</td>
                <td class="value" colspan="4">
                    {{ isset($CreatedAt) ? \Carbon\Carbon::parse($CreatedAt)->format('F d, Y \a\t g:i A') : '—' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-head">Patient Information</div>
        <table class="row">
            <tr>
                <td>
                    <div class="field-label">Patient Name</div>
                    <div class="field-value">{{ $PatientName ?? '—' }}</div>
                </td>
                <td>
                    <div class="field-label">Patient ID</div>
                    <div class="field-value">{{ $UserDetailsId ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-label">
                        @if(!empty($StudentDetails))
                        Course
                        @elseif(!empty($StaffDetails))
                        Department
                        @else
                        Role
                        @endif
                    </div>
                    <div class="field-value">
                        @if(!empty($StudentDetails) && isset($StudentDetails['Course']))
                        {{ $StudentDetails['Course'] }}
                        @elseif(!empty($StaffDetails) && isset($StaffDetails['StaffDepartment']))
                        {{ $StaffDetails['StaffDepartment'] }}
                        @else
                        —
                        @endif
                    </div>
                </td>
                <td>
                    <div class="field-label">
                        @if(!empty($StudentDetails))
                        Year Level
                        @elseif(!empty($StaffDetails))
                        Staff Role
                        @else
                        Position
                        @endif
                    </div>
                    <div class="field-value">
                        @if(!empty($StudentDetails) && isset($StudentDetails['Year']))
                        {{ $StudentDetails['Year'] }}
                        @elseif(!empty($StaffDetails) && isset($StaffDetails['StaffRole']))
                        {{ $StaffDetails['StaffRole'] }}
                        @else
                        —
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="field-label">Reason for Visit</div>
                    <div class="field-value">{{ $Reason ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Vital Signs -->
    @if(!empty($VitalSigns))
    <div class="section">
        <div class="section-head">Vital Signs</div>
        <div class="vitals-grid">
            @if(!empty($VitalSigns['Temperature']))
            <div class="vital-item">
                <div class="vital-label">Temperature</div>
                <div class="vital-value">{{ $VitalSigns['Temperature'] }}°C</div>
            </div>
            @endif
            @if(!empty($VitalSigns['BloodPressure']))
            <div class="vital-item">
                <div class="vital-label">Blood Pressure</div>
                <div class="vital-value">{{ $VitalSigns['BloodPressure'] }}</div>
            </div>
            @endif
            @if(!empty($VitalSigns['PulseRate']))
            <div class="vital-item">
                <div class="vital-label">Pulse Rate</div>
                <div class="vital-value">{{ $VitalSigns['PulseRate'] }} bpm</div>
            </div>
            @endif
            @if(!empty($VitalSigns['Height']))
            <div class="vital-item">
                <div class="vital-label">Height</div>
                <div class="vital-value">{{ $VitalSigns['Height'] }} cm</div>
            </div>
            @endif
            @if(!empty($VitalSigns['Weight']))
            <div class="vital-item">
                <div class="vital-label">Weight</div>
                <div class="vital-value">{{ $VitalSigns['Weight'] }} kg</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Chief Complaint / Symptoms -->
    @if(isset($Symptoms) && $Symptoms)
    <div class="section">
        <div class="section-head">Chief Complaint / Symptoms</div>
        <div class="full-field">
            <div class="field-value">{{ $Symptoms }}</div>
        </div>
    </div>
    @endif

    <!-- Clinical Notes -->
    <div class="section">
        <div class="section-head">Clinical Findings & Assessment</div>
        @if(isset($Findings) && $Findings)
        <div class="full-field">
            <div class="field-label">Findings / Diagnosis</div>
            <div class="field-value">{{ $Findings }}</div>
        </div>
        @endif

        @if(!empty($ActionTaken))
        <div class="full-field">
            <div class="field-label">Action Taken</div>
            <div class="action-tags">
                @foreach($ActionTaken as $action)
                <span class="action-tag">{{ $action }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($Remarks) && $Remarks)
        <div class="full-field">
            <div class="field-label">Remarks / Recommendations</div>
            <div class="field-value">{{ $Remarks }}</div>
        </div>
        @endif
    </div>

    <!-- Items / Medications Provided -->
    <div class="section">
        <div class="section-head">Medications / Items Provided</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:40%">Item / Medication</th>
                    <th style="width:15%">Quantity</th>
                    <th style="width:45%">Instructions / Notes</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($ItemsProvided) && count($ItemsProvided) > 0)
                @foreach($ItemsProvided as $item)
                <tr>
                    <td>{{ $item['Title'] ?? '—' }}</td>
                    <td>
                        {{ $item['Quantity'] ?? '—' }}
                        @if(!empty($item['UOM']))
                        {{ $item['UOM'] }}
                        @endif
                    </td>
                    <td class="notes">{{ $item['Notes'] ?? '—' }}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="3" class="empty-msg">No items provided</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Doctor Signature -->
    <div class="signature-wrap">
        <div class="signature-box">
            <div class="sig-line"></div>
            <div class="sig-name">{{ $DoctorName ?? '—' }}</div>
            <div class="sig-label">Attending Physician</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <strong>Confidential Medical Record</strong> — For Official Use Only<br>
        Generated by Clinic Management System • Ref: {{ $ReferenceNo ?? '—' }}
    </div>
</body>

</html>