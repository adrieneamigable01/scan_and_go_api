<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }

        /* Title Section Styles */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 30px;
            padding: 10px;
        }
        .header .logo { 
            width: 100px; 
            height: 100px; 
        }
        .header h1 {
            font-size: 24px;
            text-align: center;
            margin: 0;
            flex-grow: 1;
        }
        .header .school-info {
            text-align: center;
            font-size: 16px;
        }

        /* Body Section Styles */
        .body { 
            margin-top: 20px; 
            padding: 0 20px; 
        }
        
        /* Left and Right Columns for Student and Academic Info */
        .details-section { 
            margin-bottom: 20px;
        }

        .details-section h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #2d3e50;
        }

        .details-section .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .details-section .details .label {
            font-weight: bold;
            color: #333;
            width: 40%;
        }

        .details-section .details .value {
            color: #555;
            width: 55%;
        }

        /* QR Code Section */
        .logo-container {
            background-color:#ccc;
            position: absolute;
            top: 20px;
            left: -20px;
            text-align: center;
        }
        .qr-code {
            position: absolute;
            top: 20px;
            right: 20px;
            text-align: center;
        }

        .qr-code img { 
            width: 100px; 
            height: 100px; 
        }

        /* Student Profile Section */
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 2px solid #ddd;
            margin-top: 20px;
        }

        /* To-Do List Section */
        .todo-list {
            margin-top: 20px;
            padding-left: 20px;
        }

        .todo-list ul {
            list-style-type: disc;
        }

        .todo-list li {
            margin-bottom: 10px;
            font-size: 16px;
        }

        /* Clearfix for the header */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="logo-container">
        <?php
            $image_path = FCPATH . 'assets/img/an.png';
            $image_data = base64_encode(file_get_contents($image_path));
            $image_base64 = 'data:image/png;base64,' . $image_data;
        ?>
        <img src="<?php echo $image_base64 ?>" alt="School Logo" class="logo" />
    </div>
    <div class="header clearfix" style="display: flex; align-items: center; padding: 20px;">
        <h1>Student Report</h1>
        <div class="school-info">
            <p>Scan and Go</p>
            <p>Cebu Philippines</p>
            <p>Philippines 6000</p>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="qr-code">
        <img src="<?php echo $qr ?>" alt="QR Code" /> <!-- Dummy QR Code Image --> <br>
        <img src="<?php echo $student->student_image?>" alt="Student Image" class="profile-image" /> <!-- Dummy Student Profile Image -->
    </div>

    <!-- Body Section -->
    <div class="body">
        <!-- Student Details Section -->
        <div class="details-section">
            <h3>Student Details</h3>
            <div class="details">
                <div class="label">Full Name:</div>
                <div class="value"><?php echo $student->full_name?></div>
            </div>
            <div class="details">
                <div class="label">Student ID:</div>
                <div class="value"><?php echo $student->student_id?></div>
            </div>
            <div class="details">
                <div class="label">Email:</div>
                <div class="value"><?php echo $student->email?></div>
            </div>
            <div class="details">
                <div class="label">Phone:</div>
                <div class="value"><?php echo $student->mobile?></div>
            </div>
        </div>

        <!-- Academic Information Section -->
        <div class="details-section">
            <h3>Academic Information</h3>
            <div class="details">
                <div class="label">Program:</div>
                <div class="value"><?php echo $student->program?></div>
            </div>
            <div class="details">
                <div class="label">Year Level:</div>
                <div class="value"><?php echo $student->year_level?></div>
            </div>
            <div class="details">
                <div class="label">College:</div>
                <div class="value"><?php echo $student->college?></div>
            </div>
            <div class="details">
                <div class="label">Section:</div>
                <div class="value"><?php echo $student->section?></div>
            </div>
        </div>

        <!-- To-Do List Section -->
        <div class="todo-list">
            <h3>Important Instructions</h3>
            <ul>
                <li>Bring a printed copy of this report to the event.</li>
                <li>In case the face scanner doesn't work, bring a physical ID or other identification.</li>
                <li>Ensure that your student ID is visible on your document for easy verification.</li>
                <li>Arrive 15 minutes before the event start time for registration.</li>
            </ul>
        </div>

    </div>

</body>
</html>
