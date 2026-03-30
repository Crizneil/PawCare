<head>
    <meta charset="UTF-8">
    <title>OFFICIAL DAILY APPOINTMENT REPORT</title>
    <style>
        /* 1. BASE SETUP & PAGE MARGINS */
        @page {
            margin: 15mm;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #000;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* 2. PRINT & PDF FOOTER LOGIC */
        /* This ensures the footer stays at the bottom of every page in PDF */
        @media print {
            .no-print { display: none !important; }

            .page-footer-container {
                position: fixed;
                bottom: -10mm;
                left: 0;
                right: 0;
                height: 180px;
                background-color: white;
            }
        }

        /* Standard styling for the footer when viewed in browser */
        .page-footer-container {
            width: 100%;
            margin-top: 50px;
        }

        /* Space-filler to prevent table rows from overlapping the fixed footer */
        .table-footer-space {
            height: 180px;
            border: none !important;
        }

        /* 3. HEADER STYLES */
        .official-header { text-align: center; margin-bottom: 10px; }
        .official-header p { margin: 0; font-size: 13px; }
        .official-header .republic { text-transform: uppercase; font-weight: bold; font-size: 14px; }
        .official-header .location { text-transform: uppercase; font-weight: bold; font-size: 12px; }
        .official-header .office { font-weight: bold; font-size: 16px; margin-top: 8px; display: block; }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 20px 0 5px 0;
        }

        .generation-meta { text-align: center; font-size: 11px; margin-bottom: 25px; color: #333; }

        /* 4. SUMMARY BOX STYLES */
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
            width: 20%;
            text-align: center;
        }

        /* 5. MAIN TABLE STYLES */
        table.appointment-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .appointment-table th { background-color: #eee; border: 1px solid #000; padding: 8px; font-size: 11px; text-align: left; text-transform: uppercase; }
        .appointment-table td { border: 1px solid #000; padding: 8px; font-size: 11px; word-wrap: break-word; }

        /* 6. SIGNATURE STYLES */
        .sig-table { width: 100%; border: none; border-collapse: collapse; }
        .sig-box { width: 45%; text-align: center; vertical-align: bottom; }
        .sig-line { border-bottom: 1px solid #000; margin-top: 45px; margin-bottom: 5px; width: 85%; margin-left: auto; margin-right: auto; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 12px; margin: 0; }
        .footer-note { text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 20px; }
    </style>
</head>

<body>

<div class="print-wrapper">
    @if(!request()->has('pdf'))
        <div class="no-print" style="text-align: right; padding: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #555; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">Print Report</button>
            <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" style="padding: 10px 20px; background: #000; color: #fff; text-decoration: none; margin-left: 10px; border-radius: 4px; display: inline-block; font-weight: bold;">Download PDF</a>
        </div>
    @endif

    <div class="official-header">
        <p class="republic">Republic of the Philippines</p>
        <p class="location">Province of Bulacan | City of Meycauayan</p>
        <span class="office">Office of the City Veterinarian (PawCare)</span>
    </div>

    <div class="report-title">Daily Appointment & Vaccination Summary</div>
    <div class="generation-meta">
        Report Date: <strong>{{ now()->format('F d, Y') }}</strong> | Generated: {{ now()->format('h:i A') }}
    </div>

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
                <th style="width: 30%;">Service Type</th>
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
        <tfoot>
            <tr>
                <td colspan="4" class="table-footer-space"></td>
            </tr>
        </tfoot>
    </table>

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
