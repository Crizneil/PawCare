<head>
    <meta charset="UTF-8">
    <title>OFFICIAL ACTIVE PETS REPORT</title>
    <style>
        @page { margin: 15mm; }

        body {
            font-family: 'Helvetica', sans-serif;
            color: #000;
            margin: 0;
            line-height: 1.4;
        }

        /* 1. HIDE BUTTONS & FORCE FOOTER ON PRINT */
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

        /* 2. FOOTER POSITIONING (PDF vs Browser Preview) */
        .pdf-fixed-footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            height: 180px;
            background-color: white;
        }

        .preview-footer {
            width: 100%;
            margin-top: 50px;
        }

        .table-footer-space {
            height: 180px;
            border: none !important;
        }

        /* Header & Table Styles */
        .official-header { text-align: center; margin-bottom: 10px; }
        .official-header p { margin: 0; font-size: 13px; }
        .official-header .republic { text-transform: uppercase; font-weight: bold; font-size: 15px; }
        .official-header .location { text-transform: uppercase; font-weight: bold; font-size: 13px; }
        .official-header .office { font-weight: bold; font-size: 17px; margin-top: 8px; display: block; }

        .report-title { text-align: center; font-weight: bold; font-size: 18px; text-transform: uppercase; text-decoration: underline; margin: 15px 0 5px 0; }
        .generation-meta { text-align: center; font-size: 11px; margin-bottom: 20px; }

        table.appointment-table { width: 100%; border-collapse: collapse; }
        .appointment-table th { background-color: #f2f2f2; border: 1px solid #000; padding: 8px; font-size: 11px; text-align: left; text-transform: uppercase; }
        .appointment-table td { border: 1px solid #000; padding: 8px; font-size: 11px; }

        .sig-table { width: 100%; border: none; }
        .sig-box { width: 45%; text-align: center; border: none !important; vertical-align: bottom; }
        .sig-line { border-bottom: 1px solid #000; margin-top: 35px; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto; }
        .sig-name { font-weight: bold; text-transform: uppercase; font-size: 12px; margin: 0; }
        .footer-note { text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 15px; }
    </style>
</head>
<body>

@if(!$isPdf)
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

    <div class="report-title">ACTIVE PETS REPORT {{ $species ? '- ' . strtoupper($species) . 'S' : '' }}</div>
    <div class="generation-meta">
        Total Active: {{ $summary['total'] }} (Dogs: {{ $summary['dogs'] }}, Cats: {{ $summary['cats'] }}) | Date Generated: {{ now()->format('F d, Y h:i A') }}
    </div>

    <table class="appointment-table">
        <thead>
            <tr>
                <th>Pet ID</th>
                <th>Pet Name</th>
                <th>Species</th>
                <th>Breed</th>
                <th>Gender</th>
                <th>Owner Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pets as $pet)
            <tr>
                <td>{{ $pet->pet_id }}</td>
                <td>{{ $pet->name }}</td>
                <td>{{ $pet->species }}</td>
                <td>{{ $pet->breed }}</td>
                <td>{{ $pet->gender }}</td>
                <td>{{ $pet->user->name ?? $pet->owner ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center;">No active pets found.</td>
            </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="6" class="table-footer-space" style="border: none;"></td>
            </tr>
        </tfoot>
    </table>

    <div class="page-footer-container {{ $isPdf ? 'pdf-fixed-footer' : 'preview-footer' }}">
        <table class="sig-table">
            <tr>
                <td class="sig-box">
                    <p style="margin:0; font-size: 11px;">Prepared By:</p>
                    <div class="sig-line"></div>
                    <p class="sig-name">{{ auth()->user()->name ?? 'Main Admin' }}</p>
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
            This is an official document generated by the PawCare System for the City of Meycauayan.
        </div>
    </div>
</body>
