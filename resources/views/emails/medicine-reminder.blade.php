<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Reminder</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px 20px;
        }
        .medicine-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .medicine-name {
            color: #667eea;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            width: 120px;
            font-weight: 600;
            color: #666;
        }
        .detail-value {
            flex: 1;
            color: #333;
        }
        .reminder-time {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        .reminder-time .time {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 20px;
        }
        .footer {
            background: #333;
            color: #999;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .disclaimer {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩺 My Doctor</h1>
            <p>Medicine Reminder</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $reminder->schedule->medicine->user->name }}!</h2>
            
            <div class="medicine-card">
                <div class="medicine-name">
                    {{ $reminder->schedule->medicine->medicine_name }}
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Dosage:</span>
                    <span class="detail-value">
                        @if($reminder->schedule->medicine->value_per_dose)
                            {{ $reminder->schedule->medicine->value_per_dose }} {{ $reminder->schedule->medicine->unit }}
                        @else
                            As prescribed
                        @endif
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">When to take:</span>
                    <span class="detail-value">
                        {{ $reminder->schedule->medicine->ruleLabel ?? 'As prescribed' }}
                    </span>
                </div>
                
                @if($reminder->schedule->medicine->dose_limit)
                <div class="detail-row">
                    <span class="detail-label">Daily limit:</span>
                    <span class="detail-value">{{ $reminder->schedule->medicine->dose_limit }} doses</span>
                </div>
                @endif
                
                <div class="reminder-time">
                    <p style="margin: 0 0 5px 0;">⏰ Time to take your medicine</p>
                    <div class="time">{{ \Carbon\Carbon::parse($reminder->reminder_at)->format('h:i A') }}</div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/medicine/reminders" class="button">
                    View All Reminders
                </a>
            </div>
            
            <div class="disclaimer">
                <strong>⚠️ Important:</strong> This is an automated reminder from My Doctor. 
                If you have already taken this medicine, please mark it as taken in the app.
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} My Doctor. All rights reserved.</p>
            <p>
                <a href="{{ config('app.url') }}/privacy-policy">Privacy Policy</a> | 
                <a href="{{ config('app.url') }}/terms-of-service">Terms of Service</a> |
                <a href="{{ config('app.url') }}/medicine/reminders">Manage Reminders</a>
            </p>
            <p style="margin-top: 15px;">
                This email was sent to {{ $reminder->schedule->medicine->user->email }}<br>
                My Doctor - Your Complete Healthcare Companion
            </p>
        </div>
    </div>
</body>
</html>