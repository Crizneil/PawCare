<head>
    <meta charset="UTF-8">
    <title>OFFICIAL DAILY APPOINTMENT REPORT</title>
    <style>
        /* Base Setup */
        @page { margin: 15mm; }
        body { font-family: 'Helvetica', sans-serif; color: #000; line-height: 1.4; margin: 0; padding: 0; }

        /* Use standard block display instead of flex for PDF stability */
        .print-wrapper { width: 100%; }

        /* Header Styles */
        .official-header { text-align: center; margin-bottom: 10px; }
        .official-header p { margin: 0; font-size: 13px; }
        .official-header .republic { text-transform: uppercase; font-weight: bold; font-size: 14px; }
        .official-header .office { font-weight: bold; font-size: 16px; margin-top: 5px; display: block; }

        .report-title { text-align: center; font-weight: bold; font-size: 18px; text-transform: uppercase; text-decoration: underline; margin: 20px 0 5px 0; }
        .generation-meta { text-align: center; font-size: 11px; margin-bottom: 25px; color: #333; }

        /* Fixed Summary Stats Box using Table for Alignment */
        .summary-stats {
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #000;
            background-color: #fcfcfc;
        }
        .summary-header {
            background-color: #f2f2f2;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
        }
        .stat-table { width: 100%; border-collapse: collapse; }
        .stat-table td {
            padding: 10px 15px;
            font-size: 12px;
            width: 20%; /* Ensures 5 equal columns */
            text-align: center;
        }

        /* Main Appointment Table */
        table.appointment-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .appointment-table th { background-color: #eee; border: 1px solid #000; padding: 8px; font-size: 11px; text-align: left; text-transform: uppercase; }
        .appointment-table td { border: 1px solid #000; padding: 8px; font-size: 11px; word-wrap: break-word; }

        /* Footer Alignment */
        .page-footer-container { width: 100%; margin-top: 50px; }
        .sig-table { width: 100%; border: none; border-collapse: collapse; }
        .sig-box { width: 45%; text-align: center; vertical-align: bottom; }
        .sig-line { border-bottom: 1px solid #000; margin-top: 45px; margin-bottom: 5px; width: 85%; margin-left: auto; margin-right: auto; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 12px; margin: 0; }
        .footer-note { text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 30px; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="print-wrapper">
    <div class="content-area">
        @if(!request()->has('pdf'))
            <div class="no-print" style="text-align: right; padding: 20px;">
                <button onclick="window.print()" style="padding: 10px 20px; background: #555; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">Print Report</button>
                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" style="padding: 10px 20px; background: #000; color: #fff; text-decoration: none; margin-left: 10px; border-radius: 4px; display: inline-block; font-weight: bold;">Download PDF</a>
            </div>
        @endif

        <div class="official-header">
            <p class="republic">Republic of the Philippines</p>
            <p class="republic">Province of Bulacan | City of Meycauayan</p>
            <span class="office">Office of the City Veterinarian (PawCare)</span>
        </div>

        <div class="report-title">Daily Appointment & Vaccination Summary</div>
        <div class="generation-meta">
            Report Date: <strong>{{ now()->format('F d, Y') }}</strong> | Generated: {{ now()->format('h:i A') }}
        </div>

        {{-- ACCOMPLISHMENT OVERVIEW: Fixed with Table for perfect alignment --}}
        <div class="summary-stats">
            <div class="summary-header">Accomplishment Overview</div>
            <table class="stat-table">
                <tr>
                    <td>Total Appointments: <br><strong>{{ $summaryData['total'] ?? count($data) }}</strong></td>
                    <td>Anti-Rabies: <br><strong>{{ $summaryData['anti_rabies'] ?? 0 }}</strong></td>
                    <td>5-in-1: <br><strong>{{ $summaryData['five_in_one'] ?? 0 }}</strong></td>
                    <td>4-in-1: <br><strong>{{ $summaryData['four_in_one'] ?? 0 }}</strong></td>
                    <td>Deworming: <br><strong>{{ $summaryData['deworming'] ?? 0 }}</strong></td>
                </tr>
            </table>
        </div>

        <table class="appointment-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Time</th>
                    <th style="width: 30%;">Pet Owner</th>
                    <th style="width: 25%;">Pet Name</th>
                    <th style="width: 30%;">Service Rendered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $apt)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}</td>
                        <td>{{ $apt->pet->owner ?? ($apt->user->name ?? 'Guest') }}</td>
                        <td>{{ $apt->pet_name }}</td>
                        <td>{{ ucfirst($apt->service_type) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px;">No appointments recorded for this date.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-footer-container">
        <table class="sig-table">
            <tr>
                <td class="sig-box">
                    <p style="margin:0; font-size: 11px;">Prepared By:</p>
                    <div class="sig-line"></div>
                    <p class="sig-name">{{ auth()->user()->name }}</p>
                    <p style="margin:0; font-size: 10px;">Clinic Staff / Data Officer</p>
                </td>
                <td style="width: 10%;"></td>
                <td class="sig-box">
                    <p style="margin:0; font-size: 11px;">Noted By:</p>
                    <div class="sig-line"></div>
                    <p class="sig-name">DR. IMELDA E. ARGUELLES</p>
                    <p style="margin:0; font-size: 10px;">City Veterinarian</p>
                </td>
            </tr>
        </table>
        <div class="footer-note">
            This official document is generated by the PawCare System. Any alterations without authorization are strictly prohibited.
        </div>
    </div>
</div>

</body>
