<head>
    <meta charset="UTF-8">
    <title>OFFICIAL VACCINATION REPORT - {{ strtoupper($type) }}</title>
    <style>
        /* Base Setup */
        @page { margin: 15mm; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body { font-family: 'Helvetica', sans-serif; color: #000; line-height: 1.4; }

        /* Print Wrapper: Essential for forcing footer to bottom in Browser Print */
        .print-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 95vh; /* Fills page height to push footer down */
        }
        .content-area { flex: 1; }

        /* Header Styles */
        .official-header { text-align: center; margin-bottom: 20px; }
        .official-header p { margin: 0; font-size: 13px; }
        .official-header .republic { text-transform: uppercase; font-weight: bold; font-size: 15px; }
        .official-header .location { text-transform: uppercase; font-weight: bold; font-size: 13px; }
        .official-header .office { font-weight: bold; font-size: 17px; margin-top: 8px; display: block; }

        .report-title { text-align: center; font-weight: bold; font-size: 18px; text-transform: uppercase; text-decoration: underline; margin: 15px 0 5px 0; }
        .generation-meta { text-align: center; font-size: 11px; margin-bottom: 20px; }
        .period-info { text-align: center; font-size: 12px; margin-top: -10px; margin-bottom: 15px; }

        /* Table Styling */
        table.history-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .history-table th { background-color: #f2f2f2; border: 1px solid #000; padding: 8px; font-size: 11px; text-align: left; text-transform: uppercase; }
        .history-table td { border: 1px solid #000; padding: 8px; font-size: 11px; }

        /* Layout Fixes */
        table tr { page-break-inside: avoid; }
        thead { display: table-header-group; }

        /* Footer Logic */
        .pdf-footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 180px;
            background-color: white;
        }

        .preview-footer {
            width: 100%;
            margin-top: auto; /* Pushes to bottom via Flexbox */
            padding-bottom: 20px;
        }

        @media print {
            .no-print { display: none !important; }
            .print-wrapper { min-height: 98vh; }
            .preview-footer { margin-top: auto; }
        }

        /* Signature Styling */
        .sig-table { width: 100%; border: none; }
        .sig-box { width: 45%; text-align: center; border: none !important; vertical-align: bottom; }
        .sig-line { border-bottom: 1px solid #000; margin-top: 35px; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 12px; margin: 0; }
        .footer-note { text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 8px; margin-top: 10px; width: 100%; }
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
            <p class="location">Province of Bulacan</p>
            <p class="location">City of Meycauayan</p>
            <span class="office">Office of the City Veterinarian (PawCare)</span>
        </div>

        <div class="report-title">{{ $reportTitle }}</div>

        <div class="period-info">
            @if(request('start_date') && request('end_date'))
                Period: {{ \Carbon\Carbon::parse(request('start_date'))->format('M d, Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('M d, Y') }}
            @elseif(request('period') == 'today')
                Period: {{ now()->format('F d, Y') }}
            @elseif(request('period') == 'weekly')
                Period: {{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d, Y') }}
            @elseif(request('period') == 'monthly')
                Period: {{ now()->format('F Y') }}
            @else
                Period: All Records
            @endif
        </div>

        <div class="generation-meta">
            Type: {{ ucfirst($type) }} | Date Generated: {{ now()->format('F d, Y h:i A') }}
        </div>

        <table class="history-table">
            <thead>
                <tr>
                    <th>Date Administered</th>
                    <th>Pet Name</th>
                    <th>Vaccine</th>
                    <th>Batch No.</th>
                    <th>Staff</th>
                    <th>Next Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record->date_administered)->format('M d, Y') }}</td>
                        <td>{{ $record->pet->name }}</td>
                        <td>{{ $record->vaccine_name }}</td>
                        <td>{{ $record->batch_no ?? 'N/A' }}</td>
                        <td>{{ $record->staff->name ?? 'System' }}</td>
                        <td>{{ $record->next_due_date ? \Carbon\Carbon::parse($record->next_due_date)->format('M d, Y') : '--' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No vaccination records found for this selection.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="page-footer-container {{ request()->has('pdf') ? 'pdf-footer' : 'preview-footer' }}">
        <table class="sig-table">
            <tr>
                <td class="sig-box">
                    <p style="margin:0; font-size: 11px;">Prepared By:</p>
                    <div class="sig-line"></div>
                    <p class="sig-name">{{ auth()->user()->name }}</p>
                    <p style="margin:0; font-size: 10px;">Clinic Staff / Data Officer</p>
                </td>
                <td style="width: 10%; border: none;"></td>
                <td class="sig-box">
                    <p style="margin:0; font-size: 11px;">Noted By:</p>
                    <div class="sig-line"></div>
                    <p class="sig-name">DR. IMELDA E. ARGUELLES</p>
                    <p style="margin:0; font-size: 10px;">City Veterinarian</p>
                </td>
            </tr>
        </table>
        <div class="footer-note">
            This is an official document generated by the PawCare System for the City of Meycauayan.
        </div>
    </div>
</div>

</body>
