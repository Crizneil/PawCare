<!DOCTYPE html>
<html>
<head>
    <title>Medical History - {{ $pet->name }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #d35400; padding-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #d35400; }
        .report-title { font-size: 18px; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px; }
        
        .section { margin-bottom: 25px; }
        .section-title { background: #f8f9fa; padding: 8px 12px; font-weight: bold; border-left: 4px solid #d35400; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f2f2f2; text-align: left; padding: 10px; font-size: 12px; border-bottom: 1px solid #ddd; }
        td { padding: 10px; font-size: 12px; border-bottom: 1px solid #eee; }
        
        .pet-info { display: table; width: 100%; }
        .pet-info-col { display: table-cell; width: 50%; vertical-align: top; }
        .label { font-weight: bold; color: #666; font-size: 11px; }
        .value { margin-bottom: 8px; font-size: 13px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">PAWCARE</div>
    <div class="report-title">Pet Medical History Report</div>
</div>

<div class="section">
    <div class="section-title">PET INFORMATION</div>
    <div class="pet-info">
        <div class="pet-info-col">
            <div class="label">PET NAME</div>
            <div class="value">{{ $pet->name }}</div>
            
            <div class="label">PET ID</div>
            <div class="value">{{ $pet->pet_id }}</div>
            
            <div class="label">SPECIES / BREED</div>
            <div class="value">{{ $pet->species }} ({{ $pet->breed }})</div>
        </div>
        <div class="pet-info-col">
            <div class="label">OWNER</div>
            <div class="value">{{ $pet->user->name }}</div>
            
            <div class="label">CONTACT</div>
            <div class="value">{{ $pet->user->phone }}</div>
            
            <div class="label">GENDER / WEIGHT</div>
            <div class="value">{{ $pet->gender }} / {{ $pet->weight }}kg</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">VACCINATION RECORDS</div>
    <table>
        <thead>
            <tr>
                <th>VACCINE NAME</th>
                <th>DATE ADMINISTERED</th>
                <th>NEXT DUE DATE</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pet->vaccinations as $vax)
                <tr>
                    <td><strong>{{ $vax->vaccine_name }}</strong></td>
                    <td>{{ \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') }}</td>
                    <td>{{ $vax->next_due_date ? \Carbon\Carbon::parse($vax->next_due_date)->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        @if($vax->next_due_date && \Carbon\Carbon::parse($vax->next_due_date)->isPast())
                            <span style="color: #dc3545;">Overdue</span>
                        @else
                            <span style="color: #198754;">Complete</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            @if($pet->vaccinations->isEmpty())
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">No vaccination records found for this pet.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="footer">
    Generated on {{ now()->format('F d, Y h:i A') }} | PawCare Management System
</div>

</body>
</html>
