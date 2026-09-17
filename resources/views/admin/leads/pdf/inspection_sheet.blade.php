<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pre-Sanction Inspection Sheet (Annexure V-A)</title>
    <style>
        @page {
            margin: 20px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #000000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* SBI Logo & Header Table */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .top-header-table td {
            vertical-align: top;
        }
        .bank-name-text {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
        }
        .branch-name-text {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
        }
        .file-no-box {
            border: 1px solid #000;
            padding: 3px 8px;
            width: 220px;
            font-size: 11px;
            margin-left: auto;
            text-align: left;
        }

        .main-title {
            font-size: 11.5px;
            font-weight: bold;
            text-align: center;
            margin: 6px 0 8px 0;
            line-height: 1.35;
        }

        /* Table layout matching screenshot */
        .table-inspection {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }
        .table-inspection th, .table-inspection td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .table-inspection th {
            font-weight: bold;
            text-align: center;
            font-size: 10.5px;
        }

        .sno-col { width: 6%; text-align: center; font-weight: bold; }
        .part-col { width: 38%; font-weight: bold; }
        .obs-col { width: 56%; }

        .checkbox-container {
            font-size: 10px;
        }
        .chk-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            line-height: 9px;
            text-align: center;
            margin-right: 3px;
            vertical-align: middle;
            background: #ffffff;
        }

        /* Main Bottom Paragraph */
        .main-paragraph {
            border: 1px solid #000;
            border-top: none;
            padding: 6px 8px;
            font-size: 10px;
            line-height: 1.4;
            text-align: justify;
            margin-bottom: 15px;
            min-height: 80px;
        }

        .main-paragraph p {
            margin: 0 0 5px 0;
            text-indent: 0;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10.5px;
        }
        .footer-table td {
            vertical-align: top;
        }
    </style>
</head>
<body>

    @php
        $bankName = $lead->bank?->name ?? 'State Bank of India';
        $cityName = $lead->city?->name ?? '';
        $branchName = $cityName ? 'AMCC ' . strtoupper($cityName) . ' Branch' : 'AMCC BARDOLI Branch';
        $branchObsHeader = 'Observations of Branch Manager<br>' . ($cityName ? 'AMCC ' . strtoupper($cityName) : 'AMCC BARDOLI') . ' (0062744)';

        $bizName = $lead->business_name ?: ($lead->firm_name ?: '');
        $applicantName = $bld['applicant_coapplicant_guarantor_name'] ?? ($lead->name ? $lead->name . ($bizName ? ' – Prop. Of ' . $bizName : '') : '');
        $visitDate = !empty($bld['visit_office_date']) ? \Carbon\Carbon::parse($bld['visit_office_date'])->format('d/m/Y') : '';
        
        $officeAddr = $bld['office_organization_address'] ?? ($lead->business_address ?: ($lead->address ?: ''));
        $officePhone = $bld['office_phone_no'] ?? ($lead->mobile_number ?: '');

        $selectedOrgs = (array) ($bld['type_of_organization'] ?? []);
        $allOrgs = ['Partnership', 'Proprietorship', 'Pvt. Ltd.', 'Public Ltd', 'Others'];

        $selectedNature = (array) ($bld['nature_of_business'] ?? []);
        $allNature = ['TRADING', 'contractor', 'Processing', 'Builder', 'Manufacturing', 'Brokerage', 'Consultancy', 'Professional', 'Others'];

        $selectedOwnership = (array) ($bld['office_ownership'] ?? []);
        $allOwnership = ['OWNED', 'RENTED', 'LEASED'];

        $landmark = $bld['workplace_landmark'] ?? '';
        $yearsBiz = $bld['years_in_business'] ?? ($lead->business_experience ?: '');
        $designation = $bld['designation_applicant_guarantor'] ?? '';
        $whomMet = $bld['whom_met_details'] ?? '';
        $tvrResult = $bld['office_tvr_result'] ?? '';
    @endphp

    <!-- Top SBI Header Table -->
    <table class="top-header-table">
        <tr>
            <td style="width: 50px; padding-right: 8px;">
                <!-- SBI Blue Logo -->
                <svg width="42" height="42" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="50" fill="#0086cd"/>
                    <circle cx="50" cy="38" r="16" fill="#ffffff"/>
                    <rect x="44" y="38" width="12" height="48" fill="#ffffff"/>
                </svg>
            </td>
            <td style="text-align: center;">
                <div class="bank-name-text">{{ $bankName }},</div>
                <div class="branch-name-text">{{ $branchName }}</div>
                <div style="margin-top: 4px;">
                    <div class="file-no-box">
                        <strong>File No.</strong> &nbsp; {{ $lead->id }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Title -->
    <div class="main-title">
        PRE-SANCTION INSPECTION SHEET (OFFICE/BUSINESS OF THE APPLICANT / GARANTOR)<br>
        ANNEXURE V-A
    </div>

    <!-- 9 Rows Table -->
    <table class="table-inspection">
        <thead>
            <tr>
                <th class="sno-col">SI.NO.</th>
                <th class="part-col">Particulars</th>
                <th class="obs-col">{!! $branchObsHeader !!}</th>
            </tr>
        </thead>
        <tbody>
            <!-- Row 1 -->
            <tr>
                <td class="sno-col">1</td>
                <td class="part-col">Name of the Applicant / Co-Applicant/ Guarantor</td>
                <td class="obs-col text-center font-bold" style="white-space: pre-line;">{{ $applicantName }}</td>
            </tr>

            <!-- Row 2 -->
            <tr>
                <td class="sno-col">2</td>
                <td class="part-col">Visit to the Office/ Work Place of the Borrower</td>
                <td class="obs-col text-center font-bold">Date of Visit: {{ $visitDate }}</td>
            </tr>

            <!-- Row 3 -->
            <tr>
                <td class="sno-col">3</td>
                <td class="part-col">
                    Name of the Office/ Organization, address:<br>
                    <div style="text-align: right; font-weight: bold; margin-top: 4px;">Office</div>
                    <div style="margin-top: 8px;">Phone No.</div>
                </td>
                <td class="obs-col text-center font-bold" style="white-space: pre-line;">
                    {{ $officeAddr }}
                    @if($officePhone)
                        <div style="margin-top: 4px; font-weight: normal;">Phone: {{ $officePhone }}</div>
                    @endif
                </td>
            </tr>

            <!-- Row 4 -->
            <tr>
                <td class="sno-col">4</td>
                <td class="part-col">
                    For Self Employed:<br><br>
                    A &nbsp; Type of Organization<br><br><br>
                    B &nbsp; Nature of Business<br><br><br><br>
                    C &nbsp; Whether own office / rented / leased
                </td>
                <td class="obs-col">
                    <!-- 4A -->
                    <div class="checkbox-container">
                        @foreach($allOrgs as $opt)
                            @php $ch = in_array($opt, $selectedOrgs); @endphp
                            <span style="margin-right: 10px; display: inline-block;">
                                <span class="chk-box">@if($ch)<svg width="7" height="7" viewBox="0 0 12 12" style="vertical-align: top; margin-top: 1px;"><path d="M1.5 6l3 3 6-6" stroke="#000" stroke-width="2.2" fill="none"/></svg>@endif</span><strong>{{ $opt }}</strong>
                            </span>
                        @endforeach
                    </div>
                    <hr style="border: 0; border-top: 1px solid #000; margin: 4px 0;">

                    <!-- 4B -->
                    <div class="checkbox-container">
                        @foreach($allNature as $opt)
                            @php $ch = in_array($opt, $selectedNature); @endphp
                            <span style="margin-right: 8px; display: inline-block;">
                                <span class="chk-box">@if($ch)<svg width="7" height="7" viewBox="0 0 12 12" style="vertical-align: top; margin-top: 1px;"><path d="M1.5 6l3 3 6-6" stroke="#000" stroke-width="2.2" fill="none"/></svg>@endif</span><strong>{{ $opt }}</strong>
                            </span>
                        @endforeach
                    </div>
                    <hr style="border: 0; border-top: 1px solid #000; margin: 4px 0;">

                    <!-- 4C -->
                    <div class="text-center font-bold" style="font-size: 11px; margin-top: 3px;">
                        @foreach($allOwnership as $opt)
                            @php $ch = in_array($opt, $selectedOwnership); @endphp
                            <span style="margin: 0 15px;">
                                <span class="chk-box">@if($ch)<svg width="7" height="7" viewBox="0 0 12 12" style="vertical-align: top; margin-top: 1px;"><path d="M1.5 6l3 3 6-6" stroke="#000" stroke-width="2.2" fill="none"/></svg>@endif</span>{{ $opt }}
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>

            <!-- Row 5 -->
            <tr>
                <td class="sno-col">5</td>
                <td class="part-col">Land mark for Place of work</td>
                <td class="obs-col text-center font-bold">{{ $landmark }}</td>
            </tr>

            <!-- Row 6 -->
            <tr>
                <td class="sno-col">6</td>
                <td class="part-col">Number of year Service/ Business</td>
                <td class="obs-col text-center font-bold">{{ $yearsBiz }}</td>
            </tr>

            <!-- Row 7 -->
            <tr>
                <td class="sno-col">7</td>
                <td class="part-col">Designation of the APPLICANT / GUARANTOR:</td>
                <td class="obs-col text-center font-bold">{{ $designation }}</td>
            </tr>

            <!-- Row 8 -->
            <tr>
                <td class="sno-col">8</td>
                <td class="part-col">
                    Whom met (Name of the Person contacted &<br>designation)<br>
                    <div style="text-align: right; font-weight: bold; margin-top: 2px;">Pl give</div>
                    telephone nos.
                </td>
                <td class="obs-col text-center font-bold" style="vertical-align: middle;">
                    {!! $whomMet !!}
                </td>
            </tr>

            <!-- Row 9 -->
            <tr>
                <td class="sno-col">9</td>
                <td class="part-col">Office TVR Result (Positive/ Negative)</td>
                <td class="obs-col text-center font-bold" style="font-size: 11.5px; vertical-align: middle;">
                    {{ $tvrResult }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Main Bottom Paragraph (Constructed dynamically ONLY from filled database fields) -->
    <div class="main-paragraph">
        @if ($bizName || $visitDate || $officeAddr || $lead->name)
            <p>Visited the firm <strong>{{ $bizName }}</strong>{{ $visitDate ? " on {$visitDate}" : "" }}.{{ $officeAddr ? " The unit is located at " . str_replace("\n", ", ", $officeAddr) : "" }}{{ $lead->name ? " and met proprietor Mr " . $lead->name . ($bizName ? " of {$bizName}" : "") : "" }}.</p>
        @endif

        @if ($yearsBiz || $selectedNature)
            <p>The firm is engaged in {{ !empty($selectedNature) ? implode(', ', $selectedNature) : 'business' }} work. {{ $yearsBiz ? "He has approximately {$yearsBiz} of experience in this business line." : "" }} The unit was found to be operational and procures its raw materials from local suppliers/parties.</p>
        @endif

        @if ($lead->notes)
            <p>{{ $lead->notes }}</p>
        @endif

        @if ($cityName || $tvrResult)
            <p>{{ $cityName ? "The proprietor has been residing in {$cityName}." : "" }} There is board of firm sighted and checked light bill and old sales bill. {{ $tvrResult ? "Overall visit result: {$tvrResult}." : "" }}</p>
        @endif
    </div>

    <!-- Footer Signatures -->
    <table class="footer-table">
        <tr>
            <td style="width: 55%;">
                <div><strong>Place: {{ $branchName }}</strong></div>
                <div style="margin-top: 4px;"><strong>Date: - {{ $visitDate }}</strong></div>
            </td>
            <td style="width: 45%; text-align: right;">
                <div>signature &nbsp; ____________________</div>
                <div style="margin-top: 3px;"><strong>NAME: PRASHANT POL</strong></div>
                <div style="margin-top: 2px;"><strong>S.S. No. P11937</strong></div>
                <div style="margin-top: 2px;"><strong>Manager</strong></div>
                <div style="margin-top: 2px;"><strong>{{ $branchName }}</strong></div>
            </td>
        </tr>
    </table>

</body>
</html>
