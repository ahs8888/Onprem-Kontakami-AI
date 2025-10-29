<div style="padding:10px;color: #000">
    <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem">Hi {{ $name }},</h2>
    <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem">{{ $action }}</h2>
    <a href="javascript:;"
        style="background: #3943B7;border-radius: 48px;display: inline-block;text-align: center;font-size: 1.375rem;font-family: Arial;color: #fff;text-decoration: none;padding: 10px 70px;">{{ $otp }}</a>
    @if ($ignore)
        <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem;margin-bottom: 50px">If you did not request
            this,
            please ignore this email.</h2>
    @endif
    <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem;margin-bottom: 30px">This otp will expire in 2
        minutes.</h2>
    <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem;margin-bottom: 0px">Thanks,</h2>
    <h2 style="font-family: Arial;font-weight: 300;font-size: 1.375rem;margin-top: 0px">kontakami.com</h2>

</div>
