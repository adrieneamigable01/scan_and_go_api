<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Certificate container with background image */
        .certificate-container {
            width: auto; /* A4 width in landscape */
            height: auto; /* A4 height in landscape */
            padding: 10mm 15mm; /* Adjusted padding */
            box-sizing: border-box;
            text-align: center;
            border: 3px solid #000;
            background-image: url('http://localhost/scan_and_go_api/assets/img/an.png'); /* Replace with your image path */
            background-size: cover; /* Ensures the background covers the entire area */
            background-position: center; /* Centers the background image */
            background-repeat: no-repeat; /* Prevents repeating the background image */
        }

        /* Header section */
        .certificate-header {
            font-size: 28px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 10mm;
        }

        /* Body section */
        .certificate-body {
            font-size: 20px;
            margin-bottom: 10mm;
        }

        .certificate-footer {
            font-size: 12px;
            color: #666;
            margin-top: 10mm;
        }

        /* Name section */
        .name {
            font-size: 36px;
            font-weight: bold;
            color: #003366;
            margin: 8mm 0;
        }

        /* Course title section */
        .award {
            font-size: 24px;
            margin: 12mm 0;
        }

        /* Signature section */
        .signature {
            display: inline-block;
            margin-top: 15mm;
            font-size: 16px;
            padding: 5mm;
            border-top: 2px solid #000;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Certificate Title -->
        <div class="certificate-header">Certificate of Achievement</div>

        <!-- Certificate Body -->
        <div class="certificate-body">
            <p>This is to certify that</p>
            <div class="name">John Doe</div>
            <p>has successfully completed the course on</p>
            <div class="award">Advanced Web Development</div>
        </div>

        <!-- Certificate Footer -->
        <div class="certificate-footer">
            <p>Issued by: ABC Institute</p>
            <p>Date: January 5, 2025</p>
        </div>

        <!-- Signature Area -->
        <div class="signature">
            <p>Signature</p>
        </div>
    </div>
</body>
</html>
