<?php
/**
 * Plugin Name: Progepesa Contact Form
 * Description: Simple contact form with AJAX and Font Awesome icons
 * Version: 1.0.0
 * Author: Kelli Kirk
 * Text Domain: progepesa-contact
 */

// Deny direct access to the file
if (!defined('ABSPATH')) {
   exit;
}

class ProgepesaContactForm {
   private $admin_email = 'kelkirk93@gmail.com';
   private $phone = '+372 511 6788';

   public function __construct() {
       // Load CSS and JS files
       add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

       // AJAX handler form submission (for both logged-in and non-logged-in users)
       add_action('wp_ajax_submit_contact_form', array($this, 'handle_form_submission'));
       add_action('wp_ajax_nopriv_submit_contact_form', array($this, 'handle_form_submission'));

       // Shortcode for the contact form
       add_shortcode('progepesa_contact', array($this, 'contact_form_shortcode'));
   }

   // Load CSS and JS files
   public function enqueue_scripts() {
       // Font Awesome
       wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0');
   
       // Plugin CSS
       wp_enqueue_style('progepesa-contact-style', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0.0');

       // Plugin JS
       wp_enqueue_script('progepesa-contact-script', plugin_dir_url(__FILE__) . 'contact-form.js', array('jquery'), '1.0.0', true);

       // Forward PHP data to JS
       wp_localize_script('progepesa-contact-script', 'contactFormData', array(
           'ajax_url' => admin_url('admin-ajax.php'),
           'nonce' => wp_create_nonce('contact_form_nonce'),
           'sending' => 'Saadan...',
           'success' => 'Sõnum saadetud!',
           'error' => 'Midagi läks valesti. Palun proovi uuesti.',
       ));
   }

   /**
    * Shortcode - show the contact form
    */
   public function contact_form_shortcode($atts) {
       ob_start();
       ?>

       <div class="progepesa-contact-wrapper">
           <!-- Contact information -->
            <div class="contact-info">
               <h2><i class="fas fa-address-book"></i> Võta ühendust</h2>
               <div class="contact-details">
                   <div class="contact-item">
                       <i class="fas fa-envelope"></i>
                       <div class="contact-text">
                           <strong>E-post:</strong>
                           <a href="mailto:<?php echo esc_attr($this->admin_email); ?>">
                               <?php echo esc_html($this->admin_email); ?>
                           </a>
                       </div>
                   </div>

                   <div class="contact-item">
                       <i class="fas fa-phone"></i>
                       <div class="contact-text">
                           <strong>Telefon</strong>
                           <a href="tel:<?php echo esc_attr(str_replace(' ', '', $this->phone)); ?>">
                               <?php echo esc_html($this->phone); ?>
                           </a>
                       </div>
                   </div>

                   <!-- Contact form -->
                    <div class="contact-form">
                       <h3><i class="fas fa-paper-plane"></i> Saada sõnum</h3>
                       <form id="progepesa-contact-form" method="post">

                       <!-- Name field -->
                        <div class="form-group">
                           <label for="contact-name">
                               <i class="fas fa-user"></i> Nimi
                           </label>
                           <input type="text" id="contact-name" name="contact-name" placeholder="Sinu nimi" required>
                           </div>

                           <!-- Email field -->
                            <div class="form-group">
                            <label for="contact-email">
                           <i class="fas fa-envelope"></i> Email *
                       </label>
                       <input 
                           type="email" 
                           id="contact-email" 
                           name="email" 
                           placeholder="sinu@email.ee"
                           required
                       >
                   </div>

                   <!-- Subject field -->
                   <div class="form-group">
                       <label for="contact-subject">
                           <i class="fas fa-tag"></i> Teema *
                       </label>
                       <input 
                           type="text" 
                           id="contact-subject" 
                           name="subject" 
                           placeholder="Kuidas ma saan abiks olla?"
                           required
                       >
                   </div>
                           
                   <!-- Message field -->
                   <div class="form-group">
                       <label for="contact-message">
                           <i class="fas fa-comment"></i> Sõnum *
                       </label>
                       <textarea 
                           id="contact-message" 
                           name="message" 
                           rows="6"
                           placeholder="Kirjuta oma küsimus siia..."
                           required
                       ></textarea>
                   </div>

                   <!-- Submit button -->
                    <div class="form-group">
                       <button type="submit" class="submit-button">
                           <i class="fas fa-paper-plane"></i> Saada sõnum
                       </button>
                       </div>

                       <!-- Notification messages -->
                        <div id="form-messages">
                        </div>

                        </form>
                        </div>
                        </div>
                        <?php
                        return ob_get_clean();
                        }

                        /**
                           * AJAX handler - form submission
                        */

                        public function handle_form_submission() {
                           // Verify nonce
                           if(!check_ajax_referer('contact_form_nonce', 'nonce', false)) {
                               wp_send_json_error('Turvakontroll ebaõnnestus. Palun proovi uuesti.');
                               wp_die();
                           }

                           // Get form data and sanitize
                           $name = isset($_POST['name']) ? sanitize_text_field(trim($_POST['name'])) : '';
                           $email = isset($_POST['email']) ? sanitize_email(trim($_POST['email'])) : '';
                           $subject = isset($_POST['subject']) ? sanitize_text_field(trim($_POST['subject'])) : '';
                           $message = isset($_POST['message']) ? sanitize_textarea_field(trim($_POST['message'])) : '';

                           // Validate data
                           $errors = array();

                           // Name is required
                           if(empty($name)) {
                               $errors[] = 'Nimi on kohustuslik.';
                           } elseif(strlen($name) < 2) {
                               $errors[] = 'Nimi peab olema vähemalt 2 tähemärki.';
                           } elseif(strlen($name) > 100) {
                               $errors[] = 'Nimi ei saa olla pikem kui 100 tähemärki.';
                           }

                           // Email
                          if (empty($email)) {
                          $errors[] = 'E-posti aadress on kohustuslik.'; }
                          elseif (!is_email($email)) {
                          $errors[] = 'Vale formaat.'; }
                          elseif(strlen($email) > 100) {
                          $errors[] = 'E-posti aadress ei saa olla pikem kui 100 tähemärki.';
                          }
    
                         // Subject
                          if (empty($subject)) {
                          $errors[] = 'Teema on kohustuslik';
                          } elseif (strlen($subject) < 3) {
                          $errors[] = 'Teema peab olema vähemalt 3 tähemärki';
                          } elseif (strlen($subject) > 200) {
                          $errors[] = 'Teema on liiga pikk (max 200 tähemärki)';
                          }
    
                           // Message
                           if (empty($message)) {
                            $errors[] = 'Sõnum on kohustuslik';
                            } elseif (strlen($message) < 10) {
                            $errors[] = 'Sõnum peab olema vähemalt 10 tähemärki';
                            } elseif (strlen($message) > 1000) {
                             $errors[] = 'Sõnum on liiga pikk (max 1000 tähemärki)';
                              }

                              // If there are errors, return them
                              if(!empty($errors)) {
                               wp_send_json_error(implode(' ', $errors));
                               wp_die();
                              }

                              // Spam protection
                              $user_ip = $_SERVER['REMOTE_ADDR'];
                              $transient_key = 'contact_form_' . md5($user_ip);

                              // Check if the user has submitted the form last two minutes
                              if(get_transient($transient_key)) {
                               wp_send_json_error('Palun oota 2 minutit enne uue sõnumi saatmist.');
                               wp_die();
                              }

                              // Send email
                              $to = $this->admin_email;
                              $email_subject = 'Uus kontaktivormi sõnum: ' . $subject;

                              // Email body
                              $email_body = "Uus kontaktivormi sõnum:\n\n";
                              $email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                              $email_body .= "SAATJA INFO:\n";
                              $email_body .= "Nimi: " . $name . "\n";
                              $email_body .= "Email: " . $email . "\n";
                              $email_body .= "Teema: " . $subject . "\n\n";
                              $email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                              $email_body .= "SÕNUM:\n";
                              $email_body .= $message . "\n\n";
                              $email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
                              $email_body .= "TEHNILISED DETAILID:\n";
                              $email_body .= "Saadetud: " . current_time('Y-m-d H:i:s') . "\n";
                              $email_body .= "IP aadress: " . $user_ip . "\n";
                              $email_body .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
                              $email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

                              // Email headers
                              $headers = array(
                                  'Content-Type: text/plain; charset=UTF-8',
                                  'From: ' . get_bloginfo('name') . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
                                  'Reply-To: ' . $name . ' <' . $email . '>'
                              );

                              // Send email
                              $sent = wp_mail($to, $email_subject, $email_body, $headers);
                              if($sent) {
                               // Determine rate
                               set_transient($transient_key, true, MINUTE_IN_SECONDS * 2);

                               // Successful submittion log
                               error_log('Kontaktivormi sõnum saadetud: ' . $email_subject . ' - ' . $name . ' <' . $email . '>');

                               wp_send_json_success('Sõnum saadetud!');
                               wp_die();
                              } else {
                               // Log error
                               error_log('Sõnumi saatmine ebaõnnestus: ' . $email_subject . ' - ' . $name . ' <' . $email . '>');
                               wp_send_json_error('Sõnumi saatmine ebaõnnestus. Palun proovi uuesti.');
                               wp_die();
                              }
}
}

// Initialize the plugin
new ProgepesaContactForm();
