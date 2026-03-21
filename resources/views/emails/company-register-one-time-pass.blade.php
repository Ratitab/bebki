<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verification Code - RentNDrive</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #1c1b1f;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Material 3 Design System Colors */
        :root {
            --md-primary: #006a6a;
            --md-on-primary: #ffffff;
            --md-primary-container: #6ff7f6;
            --md-on-primary-container: #002020;
            --md-secondary: #4a6363;
            --md-on-secondary: #ffffff;
            --md-surface: #fafdfc;
            --md-surface-variant: #dae5e4;
            --md-on-surface: #191c1c;
            --md-on-surface-variant: #3f4948;
            --md-outline: #6f7978;
            --md-outline-variant: #bec9c8;
            --md-error: #ba1a1a;
        }

        .email-wrapper {
            width: 100%;
            max-width: 100%;
            background-color: #f5f5f5;
            padding: 40px 16px;
        }

        .email-container {
            max-width: 480px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08),
            0 4px 8px rgba(0, 0, 0, 0.04),
            0 8px 16px rgba(0, 0, 0, 0.04);
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, #006a6a 0%, #004d4d 100%);
            padding: 48px 32px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(-15deg);
        }

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 24px;
        }

        .logo-icon {
            width: 72px;
            height: 72px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-icon svg {
            width: 40px;
            height: 40px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 500;
            color: #ffffff;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .header-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 8px;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        /* Content Section */
        .content {
            padding: 40px 32px;
        }

        .greeting {
            font-size: 18px;
            color: #1c1b1f;
            font-weight: 500;
            margin-bottom: 16px;
        }

        .message {
            font-size: 15px;
            color: #49454f;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        /* OTP Container - Material 3 Elevated Card */
        .otp-container {
            background: linear-gradient(180deg, #f8fafa 0%, #f0f4f4 100%);
            border: 1px solid #dae5e4;
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            margin-bottom: 32px;
        }

        .otp-label {
            font-size: 12px;
            font-weight: 600;
            color: #006a6a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
        }

        .otp-code {
            font-family: 'SF Mono', 'Roboto Mono', 'Consolas', monospace;
            font-size: 42px;
            font-weight: 700;
            color: #006a6a;
            letter-spacing: 12px;
            padding: 16px 0;
            background: #ffffff;
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0, 106, 106, 0.08);
        }

        .otp-expiry {
            font-size: 13px;
            color: #6f7978;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .otp-expiry svg {
            width: 16px;
            height: 16px;
            opacity: 0.7;
        }

        /* Info Card */
        .info-card {
            background-color: #e6f2f2;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background-color: #006a6a;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-icon svg {
            width: 20px;
            height: 20px;
        }

        .info-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #1c1b1f;
        }

        .info-card-text {
            font-size: 14px;
            color: #49454f;
            line-height: 1.6;
        }

        /* Security Notice */
        .security-notice {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .security-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
        }

        .security-text {
            font-size: 13px;
            color: #9a3412;
            line-height: 1.5;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #dae5e4, transparent);
            margin: 32px 0;
        }

        /* Footer */
        .footer {
            padding: 24px 32px 32px;
            background-color: #f8fafa;
            border-top: 1px solid #eef1f1;
        }

        .footer-brand {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer-brand-name {
            font-size: 18px;
            font-weight: 600;
            color: #006a6a;
            letter-spacing: -0.3px;
        }

        .footer-tagline {
            font-size: 13px;
            color: #6f7978;
            margin-top: 4px;
        }

        .footer-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer-link {
            display: inline-block;
            font-size: 13px;
            color: #006a6a;
            text-decoration: none;
            padding: 8px 16px;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #004d4d;
            text-decoration: underline;
        }

        .footer-separator {
            color: #dae5e4;
        }

        .footer-copyright {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }

        .footer-address {
            margin-top: 12px;
            font-size: 11px;
            color: #b4b4b4;
            text-align: center;
        }

        /* Social Icons */
        .social-links {
            text-align: center;
            margin: 20px 0;
        }

        .social-link {
            display: inline-block;
            width: 36px;
            height: 36px;
            background-color: #e6f2f2;
            border-radius: 10px;
            margin: 0 6px;
            text-decoration: none;
            line-height: 36px;
        }

        .social-link svg {
            width: 18px;
            height: 18px;
            vertical-align: middle;
        }

        /* Responsive adjustments */
        @media only screen and (max-width: 520px) {
            .email-wrapper {
                padding: 20px 12px;
            }

            .email-container {
                border-radius: 20px;
            }

            .header {
                padding: 36px 24px 32px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 32px 24px;
            }

            .otp-code {
                font-size: 32px;
                letter-spacing: 8px;
            }

            .footer {
                padding: 20px 24px 28px;
            }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <div class="logo-icon">
                    <img src="https://rentndrive-api.carsphere.ge/assets/email-logo.jpg" alt="Logo">
                </div>
                <h1>Rent N Drive</h1>
                <p class="header-subtitle">Your Premium Car Rental Experience</p>
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello{{ isset($content['name']) ? ', ' . $content['name'] : '' }}! 👋</p>

            <p class="message">
                We received a request to verify your email address for accessing your rental orders.
                Please use the verification code below to complete the process.
            </p>

            <!-- OTP Box -->
            <div class="otp-container">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $content['otp'] ?? '000000' }}</div>
                <div class="otp-expiry">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Expires in {{ $content['expiry_minutes'] ?? '10' }} minutes</span>
                </div>
            </div>

            <!-- Info Card -->
            <div class="info-card">
                <div class="info-card-header">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#ffffff" stroke-width="2"/>
                        </svg>
                    </div>
                    <span class="info-card-title">Why do I need this code?</span>
                </div>
                <p class="info-card-text">
                    This code verifies that you're the owner of this email address and helps protect
                    your rental information from unauthorized access.
                </p>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <svg class="security-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 9V13M12 17H12.01M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p class="security-text">
                    <strong>Security tip:</strong> Never share this code with anyone. Rent N Drive team
                    will never ask you for this code over phone or email.
                </p>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #6f7978; text-align: center;">
                If you didn't request this code, you can safely ignore this email.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-brand">
                <div class="footer-brand-name">Rent N Drive</div>
                <div class="footer-tagline">Drive Your Dreams</div>
            </div>



            <div class="footer-links">
                <a href="#" class="footer-link"></a>
                <span class="footer-separator">•</span>
                <a href="#" class="footer-link"></a>
                <span class="footer-separator">•</span>
                <a href="#" class="footer-link"></a>
            </div>

            <div class="footer-copyright">
                &copy; {{ date('Y') }} Rent N Drive. All rights reserved.
            </div>

            <div class="footer-address">
                This email was sent to {{ $content['email'] ?? 'you' }}
            </div>
        </div>
    </div>
</div>
</body>
</html>
