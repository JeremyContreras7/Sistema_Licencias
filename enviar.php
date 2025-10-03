<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nombre y correo desde la sesión (no del form)
    $name = htmlspecialchars($_SESSION['nombre']);
    $email = htmlspecialchars($_SESSION['correo']);

    // Los demás campos sí vienen del formulario
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    if (empty($subject) || empty($message)) {
        echo "Por favor, complete todos los campos requeridos.";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '50615979dcf445';
        $mail->Password = '084d022f9ec7c1';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($email, $name); // ahora el remitente será el usuario logueado
        $mail->addAddress('jeremytortuguita@gmail.com', 'Jeremy');

        $mail->isHTML(true);
        $mail->Subject = "Nuevo mensaje de contacto: " . $subject;
        $mail->Body = "
            <h2>Nuevo mensaje de contacto</h2>
            <p><strong>Nombre:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Asunto:</strong> {$subject}</p>
            <p><strong>Mensaje:</strong></p>
            <p>{$message}</p>
        ";
        $mail->AltBody = "Nombre: {$name}\nEmail: {$email}\nAsunto: {$subject}\nMensaje: {$message}";

        if ($mail->send()) {
            echo "El mensaje ha sido enviado con éxito";
        } else {
            echo "El mensaje no pudo ser enviado. Inténtelo de nuevo más tarde.";
        }
    } catch (Exception $e) {
        echo "El mensaje no pudo ser enviado. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Acceso no autorizado.";
}
