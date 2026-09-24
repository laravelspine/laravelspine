<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification Code</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
    <div style="background: #fff; border-radius: 8px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="color: #1a1a2e; margin-top: 0;">Two-Factor Authentication</h2>
        <p style="color: #555; font-size: 14px;">Your verification code is:</p>
        <div style="text-align: center; margin: 24px 0;">
            <span style="display: inline-block; font-size: 36px; font-weight: 700; letter-spacing: 6px; color: #1a1a2e; background: #f0f0f5; padding: 16px 40px; border-radius: 8px;">
                {{ $code }}
            </span>
        </div>
        <p style="color: #888; font-size: 13px;">This code expires in 10 minutes. Do not share this code with anyone.</p>
        <p style="color: #888; font-size: 13px;">If you did not request this code, please ignore this email.</p>
        <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0 12px;">
        <p style="color: #aaa; font-size: 12px;">Sent by Spine CRM</p>
    </div>
</body>
</html>
