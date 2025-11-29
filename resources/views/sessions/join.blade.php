<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Session: {{ $session->title }}</title>
    <script src='https://meet.jit.si/external_api.js'></script>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }
        #meet {
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="meet"></div>
    <script>
        const domain = 'meet.jit.si';
        const options = {
            roomName: '{{ $session->meeting_link }}',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            userInfo: {
                displayName: '{{ Auth::user() ? Auth::user()->name : (Auth::guard("board")->check() ? Auth::guard("board")->user()->name : (Auth::guard("highboard")->check() ? Auth::guard("highboard")->user()->name : "Guest")) }}'
            },
            configOverwrite: { 
                startWithAudioMuted: true,
                startWithVideoMuted: true
            },
            interfaceConfigOverwrite: { 
                SHOW_JITSI_WATERMARK: false 
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
</body>
</html>
