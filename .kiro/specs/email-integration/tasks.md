# Implementation Plan: Email Integration

## Overview

This implementation plan breaks down the email integration feature into discrete coding tasks. The feature adds email notification capabilities to the ResiTech condominium management system using PHPMailer for SMTP delivery. The implementation follows a phased approach: dependencies and configuration, core email service, email templates, service integration, and testing.

## Tasks

- [x] 1. Set up dependencies and environment configuration
  - [x] 1.1 Update composer.json with PHPMailer and phpdotenv dependencies
    - Add "phpmailer/phpmailer": "^6.8" to require section
    - Add "vlucas/phpdotenv": "^5.5" to require section
    - _Requirements: 10.1, 10.2, 10.3_

  - [x] 1.2 Create .env.example file with email configuration template
    - Include MAIL_DRIVER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
    - Include MAIL_FROM_ADDRESS, MAIL_FROM_NAME, MAIL_TEST_MODE
    - Include SENDGRID_API_KEY for optional SendGrid support
    - _Requirements: 9.2, 9.3, 9.4, 9.5, 9.6, 9.7_

  - [x] 1.3 Create database migration for email_logs table
    - Create database/email_logs.sql with table schema
    - Include columns: id, recipient, subject, status, error_message, duration_ms, created_at
    - Add indexes for status, created_at, and recipient
    - _Requirements: 8.1, 8.2, 8.3_

  - [x] 1.4 Create email templates directory structure
    - Create app/views/emails/ directory
    - Create logs/emails/ directory for test mode
    - _Requirements: 3.6_

- [x] 2. Implement core EmailService class
  - [x] 2.1 Create EmailService class with configuration loading
    - Create app/services/EmailService.php
    - Implement constructor with database connection
    - Implement loadConfiguration() to read from environment variables
    - Implement validateConfiguration() to check required settings
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 9.1, 9.3, 9.4, 9.5, 9.6_

  - [x] 2.2 Implement PHPMailer initialization and SMTP connection
    - Implement initializeMailer() method
    - Implement initializePHPMailer() with SMTP configuration
    - Implement SMTP connection testing
    - Handle connection errors gracefully
    - _Requirements: 1.1, 1.6, 8.4, 8.7_

  - [x] 2.3 Implement email sending methods with retry logic
    - Implement sendHtmlEmail() public method
    - Implement sendTextEmail() public method
    - Implement private send() method with retry logic (3 attempts, exponential backoff)
    - Implement sendViaPHPMailer() for SMTP delivery
    - Support multiple recipients, CC, and BCC
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 2.9, 7.4, 7.6_

  - [ ]* 2.4 Write unit tests for EmailService core methods
    - Test configuration validation with valid and invalid settings
    - Test email sending with single and multiple recipients
    - Test retry logic with simulated failures
    - _Requirements: 2.7, 2.8, 8.6_

- [x] 3. Implement email template system
  - [x] 3.1 Create base email template
    - Create app/views/emails/base.php with HTML structure
    - Include responsive CSS for mobile devices
    - Add header with branding and footer with unsubscribe info
    - _Requirements: 3.3, 3.4, 3.6, 3.7_

  - [x] 3.2 Create payment notification email template
    - Create app/views/emails/payment_notification.php
    - Support dynamic variables: resident_name, apartment, tower, payment_concept, payment_amount, payment_month, due_date, reference, type
    - Handle three types: overdue, confirmation, reminder
    - _Requirements: 3.1, 3.5, 4.4, 4.5, 4.6_

  - [x] 3.3 Create incident notification email template
    - Create app/views/emails/incident_notification.php
    - Support dynamic variables: resident_name, incident_title, incident_description, incident_category, incident_priority, incident_status, incident_url, admin_notes
    - Handle types: created, status_changed, assigned, resolved
    - _Requirements: 3.2, 3.5, 5.5, 5.6, 5.7_

  - [x] 3.4 Create administrator notification email template
    - Create app/views/emails/admin_notification.php
    - Include resident information and link to manage incident/payment
    - Support urgent styling for high-priority incidents
    - _Requirements: 6.3, 6.4_

  - [x] 3.5 Implement template loading and variable replacement in EmailService
    - Implement loadTemplate() method
    - Implement getDefaultTemplate() fallback
    - Use PHP extract() for variable replacement
    - Handle missing templates gracefully
    - _Requirements: 3.5_

- [x] 4. Implement email logging and error handling
  - [x] 4.1 Implement email logging to database
    - Implement logEmail() method in EmailService
    - Log recipient, subject, status, error_message, duration_ms
    - Handle PDO exceptions gracefully
    - _Requirements: 8.1, 8.2, 8.3_

  - [x] 4.2 Implement test mode functionality
    - Implement logTestEmail() method
    - Write emails to logs/emails/email_YYYY-MM-DD.log
    - Return success without actually sending
    - _Requirements: 9.7, 9.8_

  - [x] 4.3 Implement error handling and logging
    - Add error_log() calls for all error conditions
    - Implement isEnabled() method to check service status
    - Ensure email failures don't block application flow
    - _Requirements: 1.5, 8.4, 8.6, 8.7_

- [x] 5. Checkpoint - Test core email functionality
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Integrate EmailService with NotificationService
  - [x] 6.1 Add EmailService to NotificationService constructor
    - Initialize EmailService instance in constructor
    - _Requirements: 7.1_

  - [x] 6.2 Implement sendPaymentEmail() method in NotificationService
    - Create private sendPaymentEmail() method
    - Support types: overdue, confirmation, reminder
    - Load payment_notification template with variables
    - Handle missing resident email gracefully
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 7.1, 7.5_

  - [x] 6.3 Update generateNotification() to send emails
    - Call sendPaymentEmail() after creating database notification
    - Ensure email failures don't prevent notification creation
    - _Requirements: 7.1, 7.5_

  - [ ]* 6.4 Write integration tests for NotificationService email sending
    - Test overdue payment email flow
    - Test email failure handling
    - _Requirements: 7.1, 7.5_

- [x] 7. Integrate EmailService with PaymentController
  - [x] 7.1 Add EmailService to PaymentController constructor
    - Initialize EmailService instance in constructor
    - _Requirements: 7.3_

  - [x] 7.2 Implement sendPaymentConfirmationEmail() method
    - Create private sendPaymentConfirmationEmail() method
    - Get resident and payment data
    - Load payment_notification template with type='confirmation'
    - Handle missing resident email gracefully
    - _Requirements: 4.2, 4.4, 4.5, 4.6, 7.3_

  - [x] 7.3 Update storePayment() to send confirmation emails
    - Call sendPaymentConfirmationEmail() after successful payment creation
    - Wrap in try-catch to prevent email errors from breaking payment flow
    - _Requirements: 7.3_

  - [ ]* 7.4 Write integration tests for payment confirmation emails
    - Test payment creation with email sending
    - Test email failure doesn't prevent payment creation
    - _Requirements: 7.3_

- [ ] 8. Integrate EmailService with IncidentController
  - [x] 8.1 Add EmailService to IncidentController constructor
    - Initialize EmailService instance in constructor
    - _Requirements: 7.2_

  - [x] 8.2 Implement sendIncidentEmail() method
    - Create private sendIncidentEmail() method
    - Support types: created, status_changed, assigned, resolved
    - Load incident_notification template with variables
    - Include incident URL for resident to view details
    - Handle missing resident email gracefully
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 7.2_

  - [x] 8.3 Update createIncidentNotification() to send emails
    - Call sendIncidentEmail() after creating database notification
    - Pass appropriate type based on action
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 7.2, 7.5_

  - [x] 8.4 Implement sendAdminNotification() method
    - Create private sendAdminNotification() method
    - Get all administrator emails
    - Load admin_notification template
    - Support urgent flag for high-priority incidents
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 8.5 Update notifyAdmins() to send emails
    - Call sendAdminNotification() after creating database notifications
    - Include incident/payment details and management link
    - _Requirements: 6.1, 6.2, 6.5_

  - [ ]* 8.6 Write integration tests for incident email notifications
    - Test incident creation email flow
    - Test status change email flow
    - Test administrator notification flow
    - _Requirements: 7.2, 7.5_

- [x] 9. Checkpoint - Test all integrations
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 10. Optional: Implement SendGrid support
  - [ ] 10.1 Implement initializeSendGrid() method
    - Initialize SendGrid client with API key
    - _Requirements: 1.2, 1.7_

  - [ ] 10.2 Implement sendViaSendGrid() method
    - Create SendGrid Mail object
    - Support multiple recipients
    - Handle SendGrid API responses
    - _Requirements: 1.7_

  - [ ]* 10.3 Write tests for SendGrid integration
    - Test SendGrid initialization
    - Test email sending via SendGrid API
    - _Requirements: 1.7_

- [x] 11. Create documentation and setup instructions
  - [x] 11.1 Update README with email setup instructions
    - Document .env configuration
    - Provide Gmail SMTP example
    - Provide SendGrid example
    - Document test mode usage
    - _Requirements: 9.2, 10.4_

  - [x] 11.2 Create email configuration guide
    - Document how to obtain Gmail app passwords
    - Document SendGrid API key setup
    - Document security best practices
    - _Requirements: 9.2, 10.4_

- [x] 12. Final checkpoint - Complete testing and verification
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Email failures should never block the main application flow
- Test mode should be used during development to avoid sending real emails
- The implementation uses PHP as specified in the design document
