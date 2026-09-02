<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $user->name ?: 'Sohni Profile' }} — Profile</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.55; background: #fff; }
        .page { padding: 38px 44px; }
        .header { position: relative; min-height: 145px; padding: 26px 30px 24px 154px; color: #fff; border-radius: 0 0 18px 18px; background: #087fe8; }
        .header::after { content: ''; position: absolute; right: 28px; top: 20px; width: 110px; height: 110px; border: 18px solid rgba(255,255,255,.1); border-radius: 50%; }
        .avatar { position: absolute; left: 30px; top: 27px; width: 100px; height: 100px; border: 5px solid #fff; border-radius: 50%; object-fit: cover; background: #d8edff; }
        .avatar-placeholder { position: absolute; left: 30px; top: 27px; width: 100px; height: 100px; border: 5px solid #fff; border-radius: 50%; background: #d8edff; color: #087fe8; text-align: center; line-height: 100px; font-size: 36px; font-weight: bold; }
        .name { margin: 0; font-size: 27px; font-weight: bold; letter-spacing: .2px; }
        .subtitle { margin-top: 5px; color: #d9f4ff; font-size: 12px; }
        .id { margin-top: 13px; font-size: 10px; color: #e5f8ff; }
        .content { padding: 26px 30px 0; }
        .section { margin-bottom: 22px; }
        .section-title { margin: 0 0 10px; padding-bottom: 6px; color: #087fe8; border-bottom: 2px solid #d8edff; font-size: 12px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        .contact { width: 100%; margin-bottom: 22px; }
        .contact td { width: 50%; padding: 5px 0; vertical-align: top; }
        .label { color: #68758a; font-size: 9px; text-transform: uppercase; letter-spacing: .7px; }
        .value { color: #172033; font-weight: bold; }
        .summary { padding: 12px 14px; color: #415169; border-left: 3px solid #08aee8; background: #f2f9ff; white-space: pre-line; }
        .items { width: 100%; border-collapse: collapse; }
        .items tr { border-bottom: 1px solid #e7edf4; }
        .items td { padding: 9px 6px 9px 0; vertical-align: top; }
        .items td:first-child { width: 40%; color: #172033; font-weight: bold; }
        .meta { color: #68758a; font-size: 10px; }
        .footer { margin-top: 28px; padding-top: 12px; color: #8a96a8; border-top: 1px solid #e7edf4; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            @if ($avatarData)
                <img class="avatar" src="{{ $avatarData }}" alt="Profile photo">
            @else
                <div class="avatar-placeholder">{{ strtoupper(substr($user->name ?: 'S', 0, 1)) }}</div>
            @endif
            <h1 class="name">{{ $user->name ?: 'Sohni Member' }}</h1>
            <div class="subtitle">Sohni community profile</div>
            @if ($user->sohni_id)<div class="id">Sohni ID: {{ $user->sohni_id }}</div>@endif
        </div>

        <div class="content">
            <table class="contact">
                <tr>
                    <td><div class="label">Email</div><div class="value">{{ $user->email ?: 'Not provided' }}</div></td>
                    <td><div class="label">Phone</div><div class="value">{{ $user->phone ?: 'Not provided' }}</div></td>
                </tr>
                <tr>
                    <td><div class="label">Address</div><div class="value">{{ $user->address ?: 'Not provided' }}</div></td>
                    <td><div class="label">Member since</div><div class="value">{{ $memberSince ?: 'Not available' }}</div></td>
                </tr>
            </table>

            @if ($user->about_me)
                <div class="section">
                    <h2 class="section-title">About Me</h2>
                    <div class="summary">{{ $user->about_me }}</div>
                </div>
            @endif

            <div class="section">
                <h2 class="section-title">Work Experience</h2>
                @if ($user->experiences && count($user->experiences))
                    <table class="items">
                        @foreach ($user->experiences as $experience)
                            <tr>
                                <td>{{ $experience['title'] ?? '' }}</td>
                                <td class="meta">
                                    {{ $experience['company'] ?? 'Independent' }}
                                    @if (!empty($experience['start_date'])) · {{ $experience['start_date'] }} — {{ $experience['end_date'] ?? 'Present' }} @endif
                                    @if (!empty($experience['description']))<br>{{ $experience['description'] }}@endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="meta">No work experience added.</div>
                @endif
            </div>

            <div class="section">
                <h2 class="section-title">Education</h2>
                @if ($user->educations->isNotEmpty())
                    <table class="items">
                        @foreach ($user->educations as $education)
                            <tr>
                                <td>{{ $education->title }}</td>
                                <td class="meta">
                                    @if ($education->completion_date) Completion: {{ $education->completion_date }}<br> @endif
                                    @if ($education->grade) Grade: {{ $education->grade }}<br> @endif
                                    @if ($education->marks) Marks: {{ $education->marks }} @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                @else
                    <div class="meta">No education records added.</div>
                @endif
            </div>

            <div class="footer">Generated from Sohni • {{ now()->format('F j, Y') }}</div>
        </div>
    </div>
</body>
</html>
