<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التحقق من المدخلات
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $appointmentDate = filter_input(INPUT_POST, 'appointmentDate', FILTER_SANITIZE_STRING);
    $service = filter_input(INPUT_POST, 'service', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    // تحقق من صحة البيانات
    if (!$fullName || !$email || !$phone || !$appointmentDate || !$service) {
        echo json_encode(['status' => 'error', 'message' => 'يرجى ملء جميع الحقول المطلوبة بشكل صحيح.']);
        exit;
    }

    // إعداد البريد
    $to = "info@alwisamclinic.com";
    $subject = "طلب حجز موعد جديد - $service";
    $headers = "From: $email\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "
        <h2>تم استقبال طلب حجز موعد جديد</h2>
        <ul>
            <li><strong>الاسم:</strong> $fullName</li>
            <li><strong>البريد الإلكتروني:</strong> $email</li>
            <li><strong>رقم الجوال:</strong> $phone</li>
            <li><strong>تاريخ الموعد:</strong> $appointmentDate</li>
            <li><strong>الخدمة:</strong> $service</li>
            <li><strong>ملاحظات:</strong> " . ($notes ?: 'لا توجد ملاحظات إضافية') . "</li>
        </ul>
    ";

    // إرسال البريد
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['status' => 'success', 'message' => 'تم إرسال الطلب بنجاح!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء إرسال البريد.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'طلب غير صالح.']);
}
?>
