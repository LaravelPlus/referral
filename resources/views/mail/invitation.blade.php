<x-mail::message>
# You've Been Invited!

**{{ $referrerName }}** has invited you to join **{{ $appName }}**.

@if($message)
> {{ $message }}
@endif

<x-mail::button :url="$referralLink">
Accept Invitation
</x-mail::button>

If you're not interested, you can safely ignore this email.

Thanks,<br>
{{ $appName }}
</x-mail::message>
