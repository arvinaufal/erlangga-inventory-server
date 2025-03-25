<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>Please click the button below to verify your email address:</p>
    <a href="{{ $frontendUrl }}?data={{ $encryptedEmail }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer;">
        Verify Email
    </a>
    <p>If you did not create an account, no further action is required.</p>
</body>
</html>