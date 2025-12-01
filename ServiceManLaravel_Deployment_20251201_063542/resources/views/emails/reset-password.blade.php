<x-mail::message>
# Password Reset Request

Hello {{ $user->first_name }},

We received a request to reset the password for your **ServiceMan** account.

If you made this request, click the button below to reset your password:

<x-mail::button :url="$url" color="primary">
Reset Password
</x-mail::button>

This password reset link will expire in **60 minutes** for security purposes.

## Didn't Request This?

If you didn't request a password reset, no action is needed. Your password will remain unchanged, and you can safely ignore this email.

**Security Tips:**
- Never share your password with anyone
- Use a strong, unique password
- Enable two-factor authentication when available

If you have any concerns about your account security, please contact our support team immediately.

Best regards,  
**The ServiceMan Team**

<x-mail::subcopy>
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:

{{ $url }}
</x-mail::subcopy>
</x-mail::message>
