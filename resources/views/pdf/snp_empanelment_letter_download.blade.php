<!DOCTYPE html>
<html>

<head>
    <title>SNP Empanelment Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
            font-size: 14px;
        }

        .date {
            text-align: right;
            margin-bottom: 30px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 30px;
        }

        .content {
            text-align: justify;
        }

        .footer {
            margin-top: 50px;
        }
    </style>
</head>

<body>

    <table style="width:100%;">
        <tr>
            <td>
                <img src="data:image/png;base64,{{ $logo1 }}" style="max-width:100px;"
                    alt="">
            </td>
            <td style="text-align:right;">
                <img src="data:image/png;base64,{{ $logo2 }}" style="max-width:100px;"
                    alt="">
            </td>

        </tr>
    </table>
    <div class="date">
        Date: {{ $current_date }}
    </div>

    <div class="title">
        TO WHOMSOEVER IT MAY CONCERN
    </div>

    <div class="content">
        <p>
            This is to certify that <strong>{{ $organization_name }}</strong> has been duly empanelled as a <strong>{{ $role_name }}</strong>
            under the Open Network for Digital Commerce (ONDC) framework, through the
            TEAM (Trade Enablement & Marketing) Portal of The National Small Industries Corporation Ltd. (NSIC), a
            Government of India enterprise.
        </p>
        <p>
            The empanelment was granted following a confirmation from ONDC based on SNP’s credentials, eligibility, and
            commitment for e-commerce enablement of Micro and Small Enterprises (MSEs) across India.
        </p>
        <p>
            As an empanelled SNP, the organization is authorized and encouraged to:
        </p>
        <ul>
            <li>Conduct outreach and awareness programs with MSEs</li>
            <li>Facilitate onboarding of MSEs onto the ONDC network via the TEAM Portal</li>
            <li>Provide handholding support including training, cataloguing, and post-onboarding assistance including
                account management, logistics and packaging</li>
            <li>Ensure compliance with ONDC protocols and TEAM initiative SOP</li>
        </ul>
        <p>
            This empanelment is valid as per the terms and conditions outlined by NSIC and may be subject to review or
            revocation in case of non-compliance. Also Activities under TEAM Scheme are incentivised by Ministry of MSME
            under RAMP Scheme for supporting MSEs.
        </p>
        <p>
            We welcome the active participation of the above-mentioned SNP in this national initiative to digitally
            empower MSMEs.
        </p>
        <p>
            MSEs are expected to provide the relevant required information to SNPs for completing the onboarding process
            on TEAM initiative and availing the benefits.
        </p>
    </div>

    <div class="footer">
        Ministry of Micro, Small and Medium Enterprises,<br>
        Government of India.
    </div>
</body>

</html>
