<?php
/**
 * Email Service
 * 
 * Provides email sending functionality with support for multiple providers
 * (PHPMailer SMTP and SendGrid API). Handles template loading, variable
 * replacement, error handling, and logging.
 * 
 * @package App\Services
 */
class EmailService {
    private $db;
    private $driver;
    private $mailer;
    private $config;
    private $enabled;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
        $this->loadConfiguration();
        $this->initializeMailer();
    }
    
    /**
     * Load email configuration from environment variables
     * 
     * @return void
     */
    private function loadConfiguration() {
        $this->config = [
            'driver' => $_ENV['MAIL_DRIVER'] ?? getenv('MAIL_DRIVER') ?: 'smtp',
            'host' => $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST'),
            'port' => $_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?: 587,
            'username' => $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME'),
            'password' => $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD'),
            'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS'),
            'from_name' => $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'ResiTech',
            'sendgrid_api_key' => $_ENV['SENDGRID_API_KEY'] ?? getenv('SENDGRID_API_KEY'),
            'test_mode' => ($_ENV['MAIL_TEST_MODE'] ?? getenv('MAIL_TEST_MODE')) === 'true'
        ];
        
        // Validate required configuration
        $this->enabled = $this->validateConfiguration();
    }
    
    /**
     * Validate email configuration
     * 
     * @return bool True if configuration is valid
     */
    private function validateConfiguration() {
        if ($this->config['test_mode']) {
            error_log("[EmailService] Running in TEST MODE - emails will be logged, not sent");
            return true;
        }
        
        if ($this->config['driver'] === 'smtp') {
            if (empty($this->config['host']) || empty($this->config['username']) || empty($this->config['password'])) {
                error_log("[EmailService] SMTP configuration incomplete - email sending disabled");
                return false;
            }
        } elseif ($this->config['driver'] === 'sendgrid') {
            if (empty($this->config['sendgrid_api_key'])) {
                error_log("[EmailService] SendGrid API key missing - email sending disabled");
                return false;
            }
        }
        
        if (empty($this->config['from_address'])) {
            error_log("[EmailService] FROM address missing - email sending disabled");
            return false;
        }
        
        return true;
    }
    
    /**
     * Initialize email provider (PHPMailer or SendGrid)
     * 
     * @return void
     */
    private function initializeMailer() {
        if (!$this->enabled || $this->config['test_mode']) {
            return;
        }
        
        try {
            if ($this->config['driver'] === 'smtp') {
                $this->initializePHPMailer();
            } elseif ($this->config['driver'] === 'sendgrid') {
                $this->initializeSendGrid();
            }
        } catch (Exception $e) {
            error_log("[EmailService] Failed to initialize mailer: " . $e->getMessage());
            $this->enabled = false;
        }
    }
    
    /**
     * Initialize PHPMailer for SMTP
     * 
     * @return void
     * @throws Exception
     */
    private function initializePHPMailer() {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['port'];
        $this->mailer->CharSet = 'UTF-8';
        
        // Test connection
        if (!$this->mailer->smtpConnect()) {
            throw new Exception("SMTP connection failed");
        }
        $this->mailer->smtpClose();
        
        error_log("[EmailService] PHPMailer initialized successfully");
    }
    
    /**
     * Initialize SendGrid API client
     * 
     * @return void
     * @throws Exception
     */
    private function initializeSendGrid() {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $this->mailer = new \SendGrid($this->config['sendgrid_api_key']);
        error_log("[EmailService] SendGrid initialized successfully");
    }
    
    /**
     * Send HTML email
     * 
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @param array $options Optional parameters (cc, bcc, attachments)
     * @return array Result with success status and message
     */
    public function sendHtmlEmail($to, $subject, $body, $options = []) {
        return $this->send($to, $subject, $body, true, $options);
    }
    
    /**
     * Send plain text email
     * 
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $body Plain text email body
     * @param array $options Optional parameters (cc, bcc, attachments)
     * @return array Result with success status and message
     */
    public function sendTextEmail($to, $subject, $body, $options = []) {
        return $this->send($to, $subject, $body, false, $options);
    }
    
    /**
     * Send email using configured provider
     * 
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $body Email body
     * @param bool $isHtml Whether body is HTML
     * @param array $options Optional parameters
     * @return array Result with success status and message
     */
    private function send($to, $subject, $body, $isHtml = true, $options = []) {
        $start_time = microtime(true);
        
        // Handle test mode
        if ($this->config['test_mode']) {
            return $this->logTestEmail($to, $subject, $body);
        }
        
        // Check if email service is enabled
        if (!$this->enabled) {
            $error = "Email service is disabled due to configuration errors";
            $this->logEmail($to, $subject, 'failure', $error);
            return ['success' => false, 'error' => $error];
        }
        
        try {
            // Retry logic
            $max_retries = 3;
            $retry_count = 0;
            $last_error = null;
            
            while ($retry_count < $max_retries) {
                try {
                    if ($this->config['driver'] === 'smtp') {
                        $result = $this->sendViaPHPMailer($to, $subject, $body, $isHtml, $options);
                    } else {
                        $result = $this->sendViaSendGrid($to, $subject, $body, $isHtml, $options);
                    }
                    
                    $duration = round((microtime(true) - $start_time) * 1000, 2);
                    error_log("[EmailService] Email sent successfully in {$duration}ms to: " . (is_array($to) ? implode(', ', $to) : $to));
                    
                    $this->logEmail($to, $subject, 'success', null, $duration);
                    return ['success' => true, 'message' => 'Email sent successfully'];
                    
                } catch (Exception $e) {
                    $last_error = $e->getMessage();
                    $retry_count++;
                    
                    if ($retry_count < $max_retries) {
                        // Exponential backoff: 1s, 2s, 4s
                        $wait_time = pow(2, $retry_count - 1);
                        error_log("[EmailService] Retry $retry_count/$max_retries after {$wait_time}s: " . $last_error);
                        sleep($wait_time);
                    }
                }
            }
            
            // All retries failed
            error_log("[EmailService] Email sending failed after $max_retries attempts: " . $last_error);
            $this->logEmail($to, $subject, 'failure', $last_error);
            return ['success' => false, 'error' => $last_error];
            
        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log("[EmailService] Unexpected error: " . $error);
            $this->logEmail($to, $subject, 'failure', $error);
            return ['success' => false, 'error' => $error];
        }
    }
    
    /**
     * Send email via PHPMailer
     * 
     * @param string|array $to Recipient(s)
     * @param string $subject Subject
     * @param string $body Body
     * @param bool $isHtml Is HTML
     * @param array $options Options
     * @return bool Success
     * @throws Exception
     */
    private function sendViaPHPMailer($to, $subject, $body, $isHtml, $options) {
        $this->mailer->clearAddresses();
        $this->mailer->clearCCs();
        $this->mailer->clearBCCs();
        $this->mailer->clearAttachments();
        
        $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
        
        // Add recipients
        if (is_array($to)) {
            foreach ($to as $email) {
                $this->mailer->addAddress($email);
            }
        } else {
            $this->mailer->addAddress($to);
        }
        
        // Add CC recipients
        if (isset($options['cc'])) {
            $cc_list = is_array($options['cc']) ? $options['cc'] : [$options['cc']];
            foreach ($cc_list as $cc) {
                $this->mailer->addCC($cc);
            }
        }
        
        // Add BCC recipients
        if (isset($options['bcc'])) {
            $bcc_list = is_array($options['bcc']) ? $options['bcc'] : [$options['bcc']];
            foreach ($bcc_list as $bcc) {
                $this->mailer->addBCC($bcc);
            }
        }
        
        $this->mailer->isHTML($isHtml);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $body;
        
        if ($isHtml) {
            $this->mailer->AltBody = strip_tags($body);
        }
        
        return $this->mailer->send();
    }
    
    /**
     * Send email via SendGrid
     * 
     * @param string|array $to Recipient(s)
     * @param string $subject Subject
     * @param string $body Body
     * @param bool $isHtml Is HTML
     * @param array $options Options
     * @return bool Success
     * @throws Exception
     */
    private function sendViaSendGrid($to, $subject, $body, $isHtml, $options) {
        $from = new \SendGrid\Mail\From($this->config['from_address'], $this->config['from_name']);
        
        // Handle multiple recipients
        $to_list = is_array($to) ? $to : [$to];
        $tos = [];
        foreach ($to_list as $email) {
            $tos[] = new \SendGrid\Mail\To($email);
        }
        
        $content_type = $isHtml ? 'text/html' : 'text/plain';
        $content = new \SendGrid\Mail\Content($content_type, $body);
        
        $mail = new \SendGrid\Mail\Mail($from, $tos[0], $subject, $content);
        
        // Add additional recipients
        for ($i = 1; $i < count($tos); $i++) {
            $mail->addTo($tos[$i]);
        }
        
        $response = $this->mailer->send($mail);
        
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            return true;
        } else {
            throw new Exception("SendGrid API error: " . $response->statusCode());
        }
    }
    
    /**
     * Load email template and replace variables
     * 
     * @param string $template_name Template file name (without .php extension)
     * @param array $variables Variables to replace in template
     * @return string Rendered HTML
     */
    public function loadTemplate($template_name, $variables = []) {
        $template_path = __DIR__ . '/../views/emails/' . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            error_log("[EmailService] Template not found: $template_path");
            return $this->getDefaultTemplate($variables);
        }
        
        // Extract variables for use in template
        extract($variables);
        
        // Capture template output
        ob_start();
        include $template_path;
        $html = ob_get_clean();
        
        return $html;
    }
    
    /**
     * Get default email template
     * 
     * @param array $variables Variables
     * @return string HTML
     */
    private function getDefaultTemplate($variables) {
        $content = isset($variables['content']) ? $variables['content'] : '';
        return "<html><body><p>$content</p></body></html>";
    }
    
    /**
     * Log email sending attempt to database
     * 
     * @param string|array $to Recipient(s)
     * @param string $subject Subject
     * @param string $status Status (success/failure)
     * @param string|null $error Error message
     * @param float|null $duration Duration in milliseconds
     * @return void
     */
    private function logEmail($to, $subject, $status, $error = null, $duration = null) {
        try {
            $recipient = is_array($to) ? implode(', ', $to) : $to;
            
            $query = "INSERT INTO email_logs (recipient, subject, status, error_message, duration_ms, created_at) 
                      VALUES (:recipient, :subject, :status, :error_message, :duration_ms, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':recipient', $recipient);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':error_message', $error);
            $stmt->bindParam(':duration_ms', $duration);
            $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("[EmailService] Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Log test mode email to file
     * 
     * @param string|array $to Recipient(s)
     * @param string $subject Subject
     * @param string $body Body
     * @return array Result
     */
    private function logTestEmail($to, $subject, $body) {
        $log_dir = __DIR__ . '/../../logs/emails';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/email_' . date('Y-m-d') . '.log';
        $recipient = is_array($to) ? implode(', ', $to) : $to;
        
        $log_entry = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $recipient,
            $subject,
            str_repeat('-', 80),
            $body
        );
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
        error_log("[EmailService] Test email logged to: $log_file");
        
        return ['success' => true, 'message' => 'Test email logged'];
    }
    
    /**
     * Check if email service is enabled
     * 
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled || $this->config['test_mode'];
    }
}
?>
