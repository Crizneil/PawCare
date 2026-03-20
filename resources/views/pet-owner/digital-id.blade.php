@extends('layout.admin') {{-- Ensure this matches your main layout file --}}

@section('content')
    <div class="container-fluid p-4 fade-in">
        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('pet-owner.pet-records') }}" class="btn btn-white rounded-circle shadow-sm me-3">
                    <i data-lucide="arrow-left" class="text-primary"></i>
                </a>
                <div>
                    <h2 class="fw-bold mb-0">Pet Digital ID</h2>
                    <p class="text-muted small mb-0">Official Vaccination & Identification Record</p>
                </div>
            </div>
            <button onclick="window.print()" class="btn btn-outline-primary rounded-pill px-4 shadow-sm d-print-none">
                <i data-lucide="printer" class="me-2" style="width: 18px;"></i> Print ID Card
            </button>
        </div>

        <div class="row justify-content-center mt-5">
        <div class="col-12 d-flex justify-content-center">

            {{-- CR80 Standard ID Card --}}
            <div class="cr80-card">

                {{-- Left Side: Pet Info --}}
                <div class="cr80-left">
                    <div class="d-flex align-items-center mb-2">
                        <img src="{{ $pet->image_url ? '/storage/' . $pet->image_url : 'https://ui-avatars.com/api/?name=' . $pet->name . '&background=fdfbf7&color=d35400' }}"
                            class="rounded-circle shadow-sm"
                            style="width: 0.5in; height: 0.5in; object-fit: cover; border: 2px solid #fff; flex-shrink: 0;"
                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($pet->name) }}&background=fdfbf7&color=d35400'">
                        <div class="ms-2" style="min-width: 0;">
                            <h1 class="cr80-title text-truncate">{{ $pet->name }}</h1>
                            <p class="cr80-subtitle text-truncate">{{ $pet->breed ?? 'Mixed/Other' }}</p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.1in;">
                        <div style="flex: 1; min-width: 0;">
                            <span class="cr80-label">Species</span>
                            <span class="cr80-value">{{ ucfirst($pet->species) }}</span>

                            <span class="cr80-label">Owner</span>
                            <span class="cr80-value">{{ mb_strimwidth($pet->user->name, 0, 15, "...") }}</span>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <span class="cr80-label">Birthdate</span>
                            <span class="cr80-value">{{ $pet->birthday ? \Carbon\Carbon::parse($pet->birthday)->format('m/d/Y') : 'N/A' }}</span>

                            <span class="cr80-label">Vax Status</span>
                            @php
                                $vaxCount = $pet->vaccinations->count();
                            @endphp
                            <span class="cr80-value" style="color: {{ $vaxCount > 0 ? '#198754' : '#dc3545' }}">
                                {{ $vaxCount > 0 ? 'Vaccinated' : 'Unvaccinated' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Right Side: QR Code --}}
                <div class="cr80-right">
                    <div style="background: white; padding: 0.05in; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 0.05in;">
                        @if(class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode'))
                                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(85)
                            ->margin(0)
                            ->generate(route('pet.public-profile', ['pet_id' => $pet->pet_id])) !!}
                        @else
                            <div style="width: 85px; height: 85px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center;">
                                QR Ready
                            </div>
                        @endif
                    </div>
                    <div style="font-size: 0.09in; font-weight: bold; letter-spacing: 1px; color: #212529;">
                        {{ $pet->pet_id }}
                    </div>
                    <div style="font-size: 0.06in; text-transform: uppercase; color: #6c757d; font-weight: bold;">
                        Scan to Verify
                    </div>
                </div>

                <div class="cr80-footer">
                    PAWCARE VETERINARY CLINIC
                </div>

            </div>

        </div>

        <div class="col-12 text-center mt-4">
            <p class="text-muted small">This ID card is formatted to CR80 dimensions (3.375" × 2.125"). Use the print button to generate a physical copy.</p>
        </div>
    </div>
    </div>

    <style>
        .ls-wide {
            letter-spacing: 2px;
        }

        /* CR80 Standard Size in inches: 3.375" × 2.125" */
        .cr80-card {
            width: 3.375in;
            height: 2.125in;
            margin: 0 auto;
            position: relative;
            background: #fdfbf7;
            border: 1px solid #e8e4d9;
            border-top: 5px solid #d35400;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            border-radius: 0.5rem;
            /* ~1/8 inch radius typical for CR80 */
            overflow: hidden;
            display: flex;
        }

        .cr80-left {
            width: 65%;
            padding: 0.15in;
            display: flex;
            flex-direction: column;
        }

        .cr80-right {
            width: 35%;
            background-color: #fff9f0;
            border-left: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.1in;
        }

        .cr80-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #212529;
            color: white;
            text-align: center;
            font-size: 0.08in;
            padding: 0.05in 0;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .cr80-title {
            font-size: 0.16in;
            font-weight: bold;
            margin: 0;
            line-height: 1.1;
        }

        .cr80-subtitle {
            font-size: 0.1in;
            color: var(--bs-primary);
            font-weight: bold;
            margin: 0 0 0.05in 0;
        }

        .cr80-label {
            font-size: 0.07in;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 0.02in;
            font-weight: bold;
            display: block;
        }

        .cr80-value {
            font-size: 0.09in;
            color: #212529;
            font-weight: bold;
            display: block;
            margin-bottom: 0.05in;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }

            body * {
                visibility: hidden;
            }

            .cr80-card,
            .cr80-card * {
                visibility: visible;
            }

            .cr80-card {
                position: absolute;
                left: 0;
                top: 0;
                box-shadow: none !important;
                border: 1px solid #ccc;
                /* Give a cut line */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection
