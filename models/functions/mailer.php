<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; 

/**
 * Logika za slanje verifikacionog email-a korisniku pomoću PHPMailer-a.
 * @param string $recipientEmail
 * @param string $token
 * @param string $recipientName
 * @return bool
 */
function sendVerificationEmail($recipientEmail, $token, $recipientName): bool {
    $mail = new PHPMailer(true);

    try {
       $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        
        // Fetch values safely from the $_ENV array
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? ''; 
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->Port       = $_ENV['MAIL_PORT'] ?? 2525;
        
        // Map encryption helper keyword dynamically
        if (isset($_ENV['MAIL_ENCRYPTION']) && $_ENV['MAIL_ENCRYPTION'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Configuration mapping for sender identity
        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@fixplan.com';
        $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'FixPlan Platform';
        $mail->setFrom($fromEmail, $fromName);
        
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'FixPlan - Verifikacija Vašeg Korisničkog Naloga';
        
        $verificationLink = "http://localhost/fixplan.github.io/verification.php?token=" . $token;

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                <h2>Dobrodošli na FixPlan, " . htmlspecialchars($recipientName) . "!</h2>
                <p>Hvala Vam što ste kreirali nalog na našoj platformi. Kako biste uspešno aktivirali Vaš nalog i prijavili se na sajt, molimo Vas da potvrdite Vašu email adresu klikom na dugme ispod:</p>
                <div style='margin: 30px 0;'>
                    <a href='{$verificationLink}' style='background-color: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Aktiviraj Moj Nalog</a>
                </div>
                <p style='color: #666; font-size: 0.9rem;'>Ako dugme ne radi, možete kopirati sledeći link direktno u Vaš pretraživač:</p>
                <p style='color: #0d6efd; font-size: 0.85rem;'>{$verificationLink}</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin-top: 30px;'>
                <p style='font-size: 0.8rem; color: #999;'>Ova poruka je automatski generisana, molimo Vas da ne odgovarate na nju.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer execution exception error log: " . $mail->ErrorInfo);
        return false;
    }
}