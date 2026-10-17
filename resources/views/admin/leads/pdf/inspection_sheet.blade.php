<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pre-Sanction Inspection Sheet (Annexure V-A)</title>
    <style>
        @page { margin: 26px 30px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.25; color: #000; }
        .center { text-align: center; }
        .bold { font-weight: bold; }

        .top-header { width: 100%; border-collapse: collapse; }
        .top-header td { vertical-align: top; }
        .bank-name { font-size: 12.5px; line-height: 1.25; }
        .file-no-box { border: 1px solid #000; width: 290px; margin: 5px auto 0 auto; padding: 2px 8px; text-align: left; font-size: 11.5px; }
        .main-title { font-size: 12px; text-align: center; margin: 9px 0 1px 0; line-height: 1.25; }

        .sheet { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .sheet th, .sheet td { border: 1px solid #000; padding: 7px 8px; vertical-align: middle; font-size: 12px; word-wrap: break-word; }
        .sheet th { font-weight: normal; text-align: center; padding: 3px 8px; }
        .sheet th.w-row-th, .w-row th { border: 0 !important; padding: 0 !important; height: 0; font-size: 0; line-height: 0; }
        .c-sno { text-align: center; font-weight: bold; }
        .c-letter { text-align: center; }
        .c-obs { text-align: center; }
        .r-tall td { padding-top: 19px; padding-bottom: 19px; }
        .r-mid td { padding-top: 13px; padding-bottom: 13px; }

        .chk { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1; }
        .opt { display: inline-block; margin: 0 6px 2px 0; white-space: nowrap; font-size: 11px; font-weight: bold; }

        .remarks { border: 1px solid #000; border-top: none; padding: 6px 8px 8px 8px; font-size: 12px; line-height: 1.3; text-align: justify; }
        .footer { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 12px; }
        .footer td { vertical-align: top; }
    </style>
</head>
<body>

@php
    $t = fn ($v) => trim((string) $v);

    // This annexure is the State Bank of India form, so the title is fixed
    // regardless of which bank is selected on the lead.
    $bankName    = 'State Bank of India';
    $branchName  = $t($bld['branch_name'] ?? '');
    $branchCode  = $t($bld['branch_code'] ?? '');
    $fileNo      = $t($bld['file_no'] ?? '');
    $managerName = $t($bld['manager_name'] ?? '');
    $managerSsNo = $t($bld['manager_ss_no'] ?? '');

    $bizName = $t($lead->business_name ?: $lead->firm_name);
    $applicantName = $t($bld['applicant_coapplicant_guarantor_name'] ?? '')
        ?: $t($lead->name . ($bizName ? ' - Prop. Of ' . $bizName : ''));

    $visit = ! empty($bld['visit_office_date']) ? \Carbon\Carbon::parse($bld['visit_office_date']) : null;
    $visitDate = $visit?->format('d/m/Y') ?? '';
    $footerDate = $visit?->format('d.m.Y') ?? '';

    $officeAddr  = $t($bld['office_organization_address'] ?? '') ?: $t($lead->business_address ?: $lead->address);
    $officePhone = $t($bld['office_phone_no'] ?? '');

    $selectedOrgs = (array) ($bld['type_of_organization'] ?? []);
    $allOrgs = ['Partnership', 'Proprietorship', 'Pvt. Ltd.', 'Public Ltd', 'Others'];
    $selectedNature = (array) ($bld['nature_of_business'] ?? []);
    $allNature = ['TRADING', 'Contractor', 'Processing', 'Builder', 'Manufacturing', 'Brokerage', 'Consultancy', 'Professional', 'Others'];
    $selectedOwnership = (array) ($bld['office_ownership'] ?? []);
    $allOwnership = ['OWNED', 'RENTED', 'LEASED'];

    $landmark    = $t($bld['workplace_landmark'] ?? '');
    $yearsBiz    = $t($bld['years_in_business'] ?? '') ?: $t($lead->business_experience);
    $designation = $t($bld['designation_applicant_guarantor'] ?? '');
    $whomMet     = $t($bld['whom_met_details'] ?? '');
    $tvrResult   = $t($bld['office_tvr_result'] ?? '');

    $branchWithCode = trim($branchName . ($branchCode ? ' (' . $branchCode . ')' : ''));
    $remarks = $t($bld['inspection_remarks'] ?? '') ?: \App\Support\InspectionSheetRemarks::generate($lead, $bld);
@endphp

    <table class="top-header">
        <tr>
            <td style="width: 56px;">
            </td>
            <td class="center">
                <div class="bank-name">
                    @if ($bankName){{ $bankName }}{{ $branchName ? ',' : '' }}@endif
                    @if ($branchName)<br>{{ $branchName }}@endif
                </div>
                <div class="file-no-box">File No. {{ $fileNo }}</div>
            </td>
            <td style="width: 56px;"></td>
        </tr>
    </table>

    <div class="main-title">
        PRE-SANCTION INSPECTION SHEET (OFFICE/BUSINESS OF THE APPLICANT / GUARANTOR)<br>
        ANNEXURE V-A
    </div>

    <table class="sheet">
        <thead>
            <tr class="w-row">
                <th style="width: 8%;"></th><th style="width: 5%;"></th><th style="width: 39%;"></th><th style="width: 48%;"></th>
            </tr>
            <tr>
                <th>SI.NO.</th>
                <th colspan="2" style="text-align: left;">Particulars</th>
                <th>Observations of Branch Manager{!! $branchWithCode ? '<br>' . e($branchWithCode) : '' !!}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="r-tall">
                <td class="c-sno">1</td>
                <td colspan="2">Name of the Applicant / Co-Applicant/ Guarantor</td>
                <td class="c-obs" style="white-space: pre-line;">{{ $applicantName }}</td>
            </tr>

            <tr class="r-tall">
                <td class="c-sno">&nbsp;</td>
                <td colspan="2">Visit to the Office/ Work Place of the Borrower</td>
                <td class="c-obs">Date of Visit: {{ $visitDate }}</td>
            </tr>

            <tr class="r-tall">
                <td class="c-sno">3</td>
                <td colspan="2">Name of the Office/ Organization, address:<br><span style="float: right; margin-right: 8px;">Office</span><br>Phone No.</td>
                <td class="c-obs" style="white-space: pre-line;">{{ $officeAddr }}@if ($officePhone)

<span style="font-weight: normal;">Phone: {{ $officePhone }}</span>@endif</td>
            </tr>

            <tr>
                <td class="c-sno" rowspan="4" style="vertical-align: top; padding-top: 6px;">4</td>
                <td colspan="3" style="padding: 4px 8px;">For Self Employed:</td>
            </tr>
            <tr class="r-mid">
                <td class="c-letter">A</td>
                <td>Type of Organization</td>
                <td class="c-obs" style="text-align: left;">@foreach ($allOrgs as $opt)<span class="opt"><span class="chk">{!! in_array($opt, $selectedOrgs) ? '&#9745;' : '&#9744;' !!}</span> {{ $opt }}</span> @endforeach</td>
            </tr>
            <tr class="r-mid">
                <td class="c-letter">B</td>
                <td>Nature of Business</td>
                <td class="c-obs" style="text-align: left;">@foreach ($allNature as $opt)<span class="opt"><span class="chk">{!! in_array($opt, $selectedNature) ? '&#9745;' : '&#9744;' !!}</span> {{ $opt }}</span> @endforeach</td>
            </tr>
            <tr class="r-mid">
                <td class="c-letter">C</td>
                <td>Whether own office / rented / leased</td>
                <td class="c-obs">@foreach ($allOwnership as $opt)<span class="opt" style="margin: 0 8px;"><span class="chk">{!! in_array($opt, $selectedOwnership) ? '&#9745;' : '&#9744;' !!}</span> {{ $opt }}</span> @endforeach</td>
            </tr>

            <tr class="r-mid">
                <td class="c-sno">5</td>
                <td colspan="2">Land mark for Place of work</td>
                <td class="c-obs">{{ $landmark }}</td>
            </tr>

            <tr class="r-mid">
                <td class="c-sno">6</td>
                <td colspan="2">Number of year Service/ Business</td>
                <td class="c-obs bold">{{ $yearsBiz }}</td>
            </tr>

            <tr class="r-mid">
                <td class="c-sno">7</td>
                <td colspan="2">Designation of the APPLICANT / GUARANTOR:</td>
                <td class="c-obs">{{ $designation }}</td>
            </tr>

            <tr class="r-tall">
                <td class="c-sno">8</td>
                <td colspan="2">Whom met (Name of the Person contacted & designation)<br><span style="float: right; margin-right: 8px;">Pl give</span><br>telephone nos.</td>
                <td class="c-obs" style="white-space: pre-line;">{{ $whomMet }}</td>
            </tr>

            <tr class="r-mid">
                <td class="c-sno">9</td>
                <td colspan="2">Office TVR Result (Positive/ Negative)</td>
                <td class="c-obs bold" style="vertical-align: top;">{{ $tvrResult }}</td>
            </tr>
        </tbody>
    </table>

    <div class="remarks">{!! nl2br(e($remarks)) !!}</div>

    <table class="footer">
        <tr>
            <td style="width: 64%;">
                @if ($branchName)<div>Place: {{ $branchName }}</div>@endif
                <div style="margin-top: 3px;">Date: - {{ $footerDate }}</div>
            </td>
            <td style="width: 36%;">
                <div>signature</div>
                @if ($managerName)<div style="margin-top: 4px;">NAME: {{ $managerName }}</div>@endif
                @if ($managerSsNo)<div>S.S. No. {{ $managerSsNo }}</div>@endif
                @if ($managerName || $managerSsNo)<div>Manager</div>@endif
                @if ($branchWithCode)<div>{{ $branchWithCode }}</div>@endif
            </td>
        </tr>
    </table>

</body>
</html>
