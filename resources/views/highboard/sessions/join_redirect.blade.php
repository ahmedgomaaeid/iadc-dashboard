<!DOCTYPE html>
<html>
<head>
    <title>Joining Meeting...</title>
    <script>
        window.onload = function() {
            // Open meeting link in new tab
            window.open("{{ $meetingUrl }}", "_blank");
            
            // Redirect current tab to interaction evaluation page
            window.location.href = "{{ $redirectUrl }}";
        }
    </script>
</head>
<body style="background-color: #f0f2f5; color: #333; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif;">
    <div style="text-align: center;">
        <h2 style="color: #6c5ffc;">Redirecting you to the meeting...</h2>
        <p>Please allow popups if the meeting doesn't open automatically.</p>
        <p>If not redirected, <a href="{{ $meetingUrl }}" target="_blank" style="color: #6c5ffc; text-decoration: underline;">click here to join meeting</a>.</p>
        <p>Or <a href="{{ $redirectUrl }}" style="color: #6c5ffc;">go to evaluation page</a>.</p>
    </div>
</body>
</html>
