<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Card</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 32px 40px;
            min-width: 320px;
            max-width: 400px;
            width: 100%;
        }

        .card-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }

        .card-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        .info-value {
            font-size: 16px;
            color: #111827;
            font-weight: 600;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .error-card {
            text-align: center;
        }

        .error-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .error-message {
            font-size: 16px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        @if($member)
            <div class="card-header">
                <h1>Member Card</h1>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value">{{ $member->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Member Number</span>
                    <span class="info-value">{{ $member->member_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="status-badge status-{{ $member->status }}">{{ $member->status }}</span>
                </div>
            </div>
        @else
            <div class="error-card">
                <div class="error-icon">&#128533;</div>
                <h1 style="margin-bottom: 8px; color: #111827;">Not Found</h1>
                <p class="error-message">{{ $error }}</p>
            </div>
        @endif
    </div>
</body>
</html>
