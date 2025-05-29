<!DOCTYPE html>
<html>
<head>
    <title>Slack Auth Callback</title>
    <script>
        // استخراج token من URL
        const params = new URLSearchParams(window.location.hash.substring(1));
        const accessToken = params.get('access_token');
        const userId = params.get('authed_user.id');
        const teamId = params.get('team.id');
        const teamName = params.get('team.name');

        if (accessToken) {
            // إرسال البيانات إلى النافذة الرئيسية
            window.opener.postMessage({
                slackAuthSuccess: true,
                user: { id: userId, access_token: accessToken },
                team: { id: teamId, name: teamName }
            }, window.opener.location.origin);

            // أغلق النافذة
            window.close();
        } else {
            // في حالة الخطأ
            window.opener.postMessage({
                slackAuthSuccess: false,
                error: 'Failed to authenticate with Slack'
            }, window.opener.location.origin);
            window.close();
        }
    </script>
</head>
<body>
<p>Processing Slack authentication...</p>
</body>
</html>
