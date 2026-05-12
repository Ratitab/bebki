<!DOCTYPE html>
<html lang="ka">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>მოგესალმებით Bebki.ge-ზე</title>
</head>

<body style="margin:0;padding:0;background:#f4f1ec;font-family:Arial,Helvetica,sans-serif;">

<div style="display:none;max-height:0;overflow:hidden;opacity:0;">
    მოგესალმებით Bebki.ge-ზე — ანგარიში წარმატებით შეიქმნა.
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
                        <td style="vertical-align:middle;padding-right:10px;">
                            <div style="width:32px;height:32px;background:rgba(255,255,255,0.15);border-radius:8px;line-height:32px;text-align:center;">
                                <span style="font-size:15px;color:#ffffff;">✦</span>
                            </div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div style="font-size:20px;font-weight:700;color:#ffffff;letter-spacing:2px;line-height:1.2;">BEBKI</div>
                            <div style="font-size:11px;color:rgba(255,255,255,0.60);letter-spacing:0.3px;">ხელნაკეთი ნივთების მარკეტპლეისი</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- ── WELCOME BADGE ── --}}
        <tr>
            <td style="padding:36px 40px 8px 40px;text-align:center;">
                <div style="display:inline-block;width:64px;height:64px;background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);border-radius:50%;line-height:64px;margin-bottom:16px;">
                    <span style="font-size:30px;">✓</span>
                </div>
                <h2 style="margin:0 0 10px 0;font-size:22px;font-weight:700;color:#1a1a1a;">
                    ანგარიში წარმატებით შეიქმნა!
                </h2>
                <p style="margin:0;font-size:15px;line-height:1.65;color:#444444;">
                    მოგესალმებით Bebki.ge-ზე.<br>
                    ახლა შეგიძლიათ დაათვალიეროთ ხელნაკეთი ნივთები და განახორციელოთ შეძენა.
                </p>
            </td>
        </tr>

        {{-- ── CTA BUTTON ── --}}
        <tr>
            <td style="padding:28px 40px 28px 40px;text-align:center;">
                <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#842c36 0%,#6b1f29 100%);border-radius:12px;">
                            <a href="{{ $content['browse_link'] ?? '#' }}"
                               style="display:inline-block;padding:14px 32px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;letter-spacing:0.3px;">
                                ნამუშევრების დათვალიერება →
                            </a>
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
            <td style="padding:24px 40px 32px 40px;background:#faf9f7;">
                <p style="margin:0 0 6px 0;font-size:13px;color:#999999;line-height:1.6;">
                    ეს ელ-ფოსტა გაეგზავნა მისამართზე:
                    <a href="mailto:{{ $content['email'] ?? '' }}" style="color:#842c36;text-decoration:none;">
                        {{ $content['email'] ?? '' }}
                    </a>
                </p>
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
