<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>დაადასტურეთ თქვენი ანგარიში</title>
</head>

<body style="margin:0;padding:0;background:#f4f1ec;font-family:Arial,Helvetica,sans-serif;">

{{-- Preheader (hidden preview text) --}}
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
    დაადასტურეთ თქვენი ანგარიში — ბებკი.
</div>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f1ec;">
<tr>
<td align="center" style="padding:32px 16px;">

    <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        {{-- ── HEADER ── --}}
        <tr>
            <td style="background:linear-gradient(135deg,#842c36 0%,#4a1520 100%);padding:16px 40px;text-align:center;">
                <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                    <tr>
                        <td style="vertical-align:middle;">
                            <div style="font-size:20px;font-weight:700;color:#ffffff;letter-spacing:2px;line-height:1.2;">BEBKI</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── BODY ── --}}
        <tr>
            <td style="padding:40px 40px 8px 40px;">
                <h2 style="margin:0 0 12px 0;font-size:22px;font-weight:700;color:#1a1a1a;">
                    დაადასტურეთ თქვენი ანგარიში
                </h2>
                <p style="margin:0 0 10px 0;font-size:15px;line-height:1.65;color:#444444;">
                    თქვენი ანგარიშის გასააქტიურებლად გამოიყენეთ ქვემოთ მოცემული ერთჯერადი კოდი.
                </p>
                <p style="margin:0;font-size:14px;line-height:1.6;color:#777777;">
                    თუ ანგარიში არ შეგიქმნიათ, შეგიძლიათ დააიგნოროთ ეს ელ-ფოსტა.
                </p>
            </td>
        </tr>

        {{-- ── OTP BOX ── --}}
        <tr>
            <td style="padding:28px 40px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background:linear-gradient(180deg,#fdf8f8 0%,#f9f0f1 100%);border:1px solid rgba(132,44,54,0.15);border-radius:14px;padding:32px 24px;text-align:center;">
                            <div style="font-size:11px;font-weight:700;color:#842c36;text-transform:uppercase;letter-spacing:2px;margin-bottom:16px;">
                                ერთჯერადი კოდი
                            </div>
                            <div style="font-family:'Courier New',Courier,monospace;font-size:44px;font-weight:700;color:#842c36;letter-spacing:14px;background:#ffffff;border-radius:10px;padding:16px 8px;margin-bottom:16px;box-shadow:0 2px 8px rgba(132,44,54,0.10);">
                                {{ $content['otp'] ?? '000000' }}
                            </div>
                            <div style="font-size:13px;color:#999999;">
                                ⏱ კოდი მოქმედია {{ $content['expiry_minutes'] ?? '10' }} წუთის განმავლობაში
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── DIVIDER ── --}}
        <tr>
            <td style="padding:0 40px;">
                <hr style="border:none;border-top:1px solid #eeebe6;margin:0;">
            </td>
        </tr>

        {{-- ── FOOTER ── --}}
        <tr>
            <td style="padding:24px 40px 32px 40px;background:#faf9f7;border-top:none;">
                
                <p style="margin:0;font-size:12px;color:#bbbbbb;line-height:1.5;">
                    © {{ date('Y') }} შპს ბებკი &nbsp;·&nbsp; თბილისი, საქართველო &nbsp;·&nbsp;
                    <a href="mailto:info@bebki.ge" style="color:#842c36;text-decoration:none;">info@bebki.ge</a>
                </p>
            </td>
        </tr>

    </table>

</td>
</tr>
</table>

</body>
</html>
