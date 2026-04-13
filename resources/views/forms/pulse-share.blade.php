<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ===== OpenGraph Tags (LinkedIn scrapes these) ===== --}}
    <meta property="og:type"        content="website">
    @php
        $isPeaks = isset($subdomain) && $subdomain === 'peaks';
        $title = $isPeaks ? "I'm attending Peaks Event! 🎉" : "I'm attending Pulse Event — April 21! 🎉";
        $desc = $isPeaks ? "hi i will attend peaks event #peaks #iadcsuez" : "hi i will attend pulse event in 21 april #pulse #iadcsuez";
        $hashtags = $isPeaks ? "#peaks &nbsp; #iadcsuez" : "#pulse &nbsp; #iadcsuez";
        $eventName = $isPeaks ? "⚡ Peaks Event" : "⚡ Pulse Event";
    @endphp
    <meta property="og:title"       content="{{ $title }}">
    <meta property="og:description" content="{{ $desc }}">
    @if($imagePath)
    <meta property="og:image"       content="{{ asset('storage/' . $imagePath) }}">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type"   content="image/jpeg">
    @endif
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:site_name"   content="IADC Suez">

    {{-- Twitter Card fallback --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{{ $title }}">
    <meta name="twitter:description" content="{{ $desc }}">
    @if($imagePath)
    <meta name="twitter:image"       content="{{ asset('storage/' . $imagePath) }}">
    @endif

    <title>{{ $isPeaks ? 'Peaks' : 'Pulse' }} Event Registration — IADC Suez</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
            text-align: center;
        }
        .card-banner {
            background: linear-gradient(135deg, #b4120d, #e83d3d);
            padding: 28px 24px 20px;
            color: white;
        }
        .card-banner .event-label {
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.85;
            margin-bottom: 8px;
        }
        .card-banner h1 {
            font-size: 1.7rem;
            font-weight: 700;
        }
        .card-banner .date-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 0.88rem;
            margin-top: 10px;
        }
        .card-photo {
            padding: 28px 28px 0;
        }
        .card-photo img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 16px;
            border: 4px solid #f1f5f9;
        }
        .card-body {
            padding: 24px 28px 32px;
        }
        .attending-text {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .hashtags {
            font-size: 0.9rem;
            color: #0a66c2;
            margin-bottom: 24px;
        }
        .btn-linkedin {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #0a66c2;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(10, 102, 194, 0.4);
        }
        .btn-linkedin:hover {
            background: #004182;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(10, 102, 194, 0.55);
            color: white;
        }
        .footer-logo {
            margin-top: 20px;
            font-size: 0.78rem;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-banner">
            <div class="event-label">IADC Suez Presents</div>
            <h1>{{ $eventName }}</h1>
            @if(!$isPeaks)
            <div class="date-badge"><i class="fas fa-calendar-alt me-1"></i> April 21, 2026</div>
            @endif
        </div>

        @if($imagePath)
        <div class="card-photo">
            <img src="{{ asset('storage/' . $imagePath) }}" alt="Attendee Photo">
        </div>
        @endif

        <div class="card-body">
            <p class="attending-text">I'm going to {{ $isPeaks ? 'Peaks' : 'Pulse' }}! 🎉</p>
            <p class="hashtags">{!! $hashtags !!}</p>

            @php
                $shareText = $desc;
                $shareUrl  = url()->current();
                $linkedInShareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($shareUrl);
            @endphp

            <a href="{{ $linkedInShareUrl }}" target="_blank" rel="noopener noreferrer" class="btn-linkedin">
                <i class="fab fa-linkedin"></i>
                Share on LinkedIn
            </a>

            <p class="footer-logo">IADC Suez · <a href="https://iadcsuez.org" style="color:#b4120d;text-decoration:none;">iadcsuez.org</a></p>
        </div>
    </div>
</body>
</html>
