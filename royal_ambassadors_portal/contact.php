<?php
$page_title = "Contact Us";
require_once "includes/db_connect.php";
require_once "includes/functions.php";

$form_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic form processing. In a real app, this would send an email.
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);

    // For now, just show a success message.
    $form_message = "Thank you for your message, " . htmlspecialchars($name) . "! We will get back to you shortly.";
}


// Fetch settings for contact page
$settings = [];
$result = $mysqli->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('org_email', 'org_phone', 'org_address')");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$mysqli->close();

include 'includes/header.php';
?>
<link rel="stylesheet" href="styles/contact.css" />
<style>
    .contact-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
    .contact-content { display: flex; gap: 40px; margin-top: 30px; }
    .contact-form { flex: 2; }
    .contact-info { flex: 1; }
    @media (max-width: 768px) { .contact-content { flex-direction: column; } }
</style>

<div class="contact-container">
    <h1>Contact Us</h1>
    <p class="subtext">We're here to assist you. Please use the form below or reach us through our contact details.</p>

    <div class="contact-content">
      <div class="contact-form">
        <?php if(!empty($form_message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><?php echo $form_message; ?></div>
        <?php endif; ?>
        <form action="contact.php" method="POST">
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" id="name" name="name" required placeholder="Enter your full name" />
            </div>
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" required placeholder="Enter your email address" />
            </div>
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" id="subject" name="subject" required placeholder="Enter subject" />
            </div>
            <div class="form-group">
              <label for="message">Your Message</label>
              <textarea id="message" name="message" rows="6" required placeholder="Type your message here..."></textarea>
            </div>
            <button type="submit">Send Message</button>
        </form>
      </div>

      <div class="contact-info">
        <h2>Reach Us</h2>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($settings['org_email'] ?? ''); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($settings['org_phone'] ?? ''); ?></p>
        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($settings['org_address'] ?? '')); ?></p>
        <div class="map">
          <!-- In a real implementation, the map embed URL would also come from settings -->
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126391.60788655745!2d3.241997841849274!3d7.156964356087157!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1039d06d02c1f68d%3A0x4ddcc6c0bc489b5b!2sOgun%20State!5e0!3m2!1sen!2sng!4v1653322136792!5m2!1sen!2sng"
            width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
