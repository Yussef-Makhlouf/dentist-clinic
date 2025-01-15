<?php
// Database configuration
$dbHost = 'localhost';
$dbName = 'clinic_db';
$dbUser = 'root';
$dbPass = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $appointmentDate = filter_input(INPUT_POST, 'appointmentDate', FILTER_SANITIZE_STRING);
    $service = filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    // Validation checks
    if (!preg_match("/^[\x{0600}-\x{06FF}\s]{3,50}$/u", $fullName)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid name format']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        exit;
    }

    if (!preg_match("/^(05)[0-9]{8}$/", $phone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid phone number']);
        exit;
    }

    try {
        // Insert booking into database
        $sql = "INSERT INTO bookings (full_name, email, phone, appointment_date, service, notes, status, created_at) 
                VALUES (:fullName, :email, :phone, :appointmentDate, :service, :notes, 'pending', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'fullName' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'appointmentDate' => $appointmentDate,
            'service' => $service,
            'notes' => $notes
        ]);

        // Send confirmation email
        $to = $email;
        $subject = "تأكيد حجز موعد - عيادة الوسام المتحدة";
        
        $message = "
        <html dir='rtl'>
        <head>
            <title>تأكيد حجز موعد</title>
        </head>
        <body>
            <h2>شكراً لك {$fullName}</h2>
            <p>تم استلام طلب حجز موعدك بنجاح</p>
            <h3>تفاصيل الموعد:</h3>
            <ul>
                <li>الخدمة: {$service}</li>
                <li>التاريخ: {$appointmentDate}</li>
            </ul>
            <p>سنقوم بالتواصل معك قريباً على رقم الجوال {$phone} لتأكيد موعدك</p>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: عيادة الوسام المتحدة <no-reply@wissamclinic.com>' . "\r\n";

        mail($to, $subject, $message, $headers);

        // Send admin notification
        $adminEmail = "admin@wissamclinic.com";
        $adminSubject = "حجز موعد جديد";
        mail($adminEmail, $adminSubject, $message, $headers);

        // Return success response
        echo json_encode([
            'status' => 'success',
            'message' => 'تم استلام طلب الحجز بنجاح'
        ]);

    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'حدث خطأ في حفظ البيانات'
        ]);
    }
}

// Database table structure
/*
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    appointment_date DATE NOT NULL,
    service VARCHAR(100) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/
?>
