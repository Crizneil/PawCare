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
    <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
        Republic of the Philippines<br>
        Province of Bulacan<br>
        <strong>CITY OF MEYCAUAYAN</strong><br>
        <strong>OFFICE OF THE CITY VETERINARIAN</strong>
    </div>
    <div style="font-size: 10px; color: #444; margin-bottom: 10px; line-height: 1.4;">
        Meycauayan City Hall, Gulod Road, Brgy. Camalig, City of Meycauayan, Bulacan<br>
        Contact: (044) 228 2825 | FB: @CityVeterinaryOfficeMeycauayan
    </div>
    <div class="report-title" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px; font-weight: bold;">Official Pet Medical History Record</div>
</div>

<div class="section">
    <div class="section-title">I. PATIENT & OWNER INFORMATION</div>
    <div class="pet-info">
        <div class="pet-info-col">
            <div class="label">PET NAME</div>
            <div class="value" style="font-size: 16px; font-weight: bold; color: #000;">{{ $pet->name }}</div>
            
            <div class="label">OFFICIAL PET ID</div>
            <div class="value">{{ $pet->pet_id }}</div>
            
            <div class="label">SPECIES / BREED</div>
            <div class="value">{{ $pet->species }} / {{ $pet->breed }}</div>

            <div class="label">GENDER / WEIGHT</div>
            <div class="value">{{ $pet->gender }} / {{ $pet->weight ?? '---' }}kg</div>
        </div>
        <div class="pet-info-col">
            <div class="label">REGISTERED OWNER</div>
            <div class="value" style="font-size: 14px; font-weight: bold;">{{ $pet->user->name }}</div>
            
            <div class="label">CONTACT NUMBER</div>
            <div class="value">{{ $pet->user->phone }}</div>
            
            <div class="label">RESIDENTIAL ADDRESS</div>
            <div class="value" style="font-size: 11px;">
                {{ $pet->user->house_number }} {{ $pet->user->street }}, {{ $pet->user->barangay }},<br>
                {{ $pet->user->city }}, {{ $pet->user->province }}
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">II. VACCINATION & IMMUNIZATION HISTORY</div>
    <table style="border: 1px solid #dee2e6;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="border: 1px solid #dee2e6;">VACCINE ADMINISTERED</th>
                <th style="border: 1px solid #dee2e6;">DATE ADMINISTERED</th>
                <th style="border: 1px solid #dee2e6;">NEXT DUE DATE</th>
                <th style="border: 1px solid #dee2e6;">VET / STAFF</th>
                <th style="border: 1px solid #dee2e6;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pet->vaccinations as $vax)
                <tr>
                    <td style="border: 1px solid #dee2e6;"><strong>{{ $vax->vaccine_name }}</strong></td>
                    <td style="border: 1px solid #dee2e6;">{{ \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $vax->next_due_date ? \Carbon\Carbon::parse($vax->next_due_date)->format('M d, Y') : 'N/A' }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $vax->staff->name ?? 'System' }}</td>
                    <td style="border: 1px solid #dee2e6; text-align: center;">
                        @if($vax->next_due_date && \Carbon\Carbon::parse($vax->next_due_date)->isPast())
                            <span style="color: #dc3545; font-weight: bold;">[!] OVERDUE</span>
                        @else
                            <span style="color: #198754; font-weight: bold;">PASSED</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            @if($pet->vaccinations->isEmpty())
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #999;">No official vaccination records found in the registry.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

{{-- Signature & Certification Section --}}
<div style="margin-top: 50px; width: 100%;">
    <div style="float: left; width: 45%;">
        <div style="font-size: 10px; color: #666; font-style: italic; margin-bottom: 20px;">
            Certified True and Correct:
        </div>
        <div style="border-bottom: 1px solid #000; width: 200px; margin-top: 30px;"></div>
        <div style="font-size: 11px; font-weight: bold;">Authorized Staff Signature</div>
        <div style="font-size: 10px; color: #666;">Office of the City Veterinarian</div>
        <div style="font-size: 9px; color: #888;">City of Meycauayan</div>
    </div>
    <div style="float: right; width: 45%; text-align: right;">
        <div style="font-size: 10px; color: #666; font-style: italic; margin-bottom: 20px;">
            Noted By:
        </div>
        <div style="border-bottom: 1px solid #000; width: 200px; margin-top: 30px; margin-left: auto;"></div>
        <div style="font-size: 11px; font-weight: bold;">Attending Veterinarian</div>
        <div style="font-size: 10px; color: #666;">PRC License No: ___________</div>
    </div>
    <div style="clear: both;"></div>
</div>

<div style="margin-top: 40px; padding: 10px; border: 1px dashed #ccc; background-color: #fcfcfc;">
    <p style="font-size: 9px; color: #777; margin: 0; line-height: 1.3;">
        <strong>NOTICE:</strong> This document serves as an official medical record from the **Office of the City Veterinarian - Meycauayan**. 
        Any unauthorized alteration, tampering, or reproduction of this record is strictly prohibited and may be subject to legal action under applicable laws. 
        For verification, please Scan the QR code on the Pet's Digital ID.
    </p>
</div>

<div class="footer">
    Record Generated: {{ now()->format('F d, Y h:i A') }} | Tracking ID: {{ strtoupper(uniqid()) }} | <span style="font-weight: bold;">Made with PawCare Management System</span>
</div>

</body>
</html>
