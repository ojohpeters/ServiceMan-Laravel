<x-mail::message>
# Welcome to ServiceMan, {{ $user->first_name }}! 🎉

Thank you for joining **ServiceMan** - your trusted platform for professional on-demand services.

We're excited to have you as a **{{ ucfirst(strtolower($user->user_type)) }}** on our platform!

## Verify Your Email Address

To get started and access all features, please verify your email address by clicking the button below:

<x-mail::button :url="$url" color="success">
Verify Email Address
</x-mail::button>

This verification link will expire in 24 hours for security purposes.

### What's Next?

@if($user->user_type === 'CLIENT')
- Browse our wide selection of professional services
- Book trusted servicemen for your needs
- Track your service requests in real-time
- Rate and review your experience
@elseif($user->user_type === 'SERVICEMAN')
- Complete your professional profile
- Start receiving service requests
- Build your reputation with ratings
- Grow your business with us
@else
- Manage the ServiceMan platform
- Oversee service requests
- Monitor platform performance
- Support users and servicemen
@endif

If you didn't create this account, please ignore this email or contact our support team.

**Need Help?**  
Visit our [Help Center]({{ url('/contact') }}) or reply to this email.

Best regards,  
**The ServiceMan Team**

<x-mail::subcopy>
If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:

{{ $url }}
</x-mail::subcopy>
</x-mail::message>
