<?php
session_set_cookie_params([
  'lifetime' => 0, // Session ends when browser closes
  'path' => '/',
  'domain' => '', // Current domain
  'secure' => true, // Only over HTTPS
  'httponly' => true, // Prevent JavaScript access
  'samesite' => 'Strict' // Prevent CSRF attacks
]);

session_start();
require 'db.php'; // Database connection
$message = '';

// Generate a secure UUID (matches CHAR(36) in your database)
function generateUUID() {
    return bin2hex(random_bytes(16)); // Generates a 32-character hexadecimal ID
}

// Function to update active users
function updateActiveUser($conn, $user_id) {
    $stmt = $conn->prepare("INSERT INTO active_users (user_id, last_active) 
                            VALUES (:user_id, NOW()) 
                            ON DUPLICATE KEY UPDATE last_active = NOW()");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['logemail'] ?? '');
        $password = trim($_POST['logpass'] ?? '');

        if (!empty($email) && !empty($password)) {
            try {
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];  // Store secure UUID
                    $_SESSION['username'] = $user['name']; // Store username
                    
                    // ✅ Update active user status
                    updateActiveUser($conn, $user['user_id']);

                    header("Location: main.php");
                    exit;
                } else {
                    $message = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Please fill in all fields.";
        }
    } elseif ($action === 'signup') {
        $name = trim($_POST['regname'] ?? '');
        $email = trim($_POST['regemail'] ?? '');
        $password = trim($_POST['regpass'] ?? '');

        if (!empty($name) && !empty($email) && !empty($password)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email format.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $user_id = generateUUID(); // Generate a unique UUID for the user

                try {
                    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $message = "Email already registered.";
                    } else {
                        $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, password) VALUES (:user_id, :name, :email, :password)");
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->bindParam(':name', $name);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':password', $hashedPassword);

                        if ($stmt->execute()) {
                            // ✅ Update active user status
                            updateActiveUser($conn, $user_id);

                            $message = "Signup successful.";
                            header("Location: index.php");
                            exit;
                        } else {
                            $message = "Error occurred during registration.";
                        }
                    }
                } catch (PDOException $e) {
                    $message = "Error: " . $e->getMessage();
                }
            }
        } else {
            $message = "Please fill in all fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">

  <title>Sign Up and Register Page</title>
</head>
<body>
  <a href="https://front.codes/" class="logo" target="_blank">
    <img src="https://assets.codepen.io/1462889/fcy.png" alt="">
  </a>
  <style>
  @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900');
  * {
    box-sizing: border-box;
  }
  html {
    font-family: sans-serif;
    line-height: 1.15;
    -webkit-text-size-adjust: 100%;
    -webkit-tap-highlight-color: transparent;
  }
  div {
    display: block;
    unicode-bidi: isolate;
  }
  body{
    font-family: 'Poppins', sans-serif;
    font-weight: 300;
    font-size: 15px;
    line-height: 1.7;
    color: #c4c3ca;
    background-color: #1f2029;
    overflow-x: hidden;
  }
  a {
    cursor: pointer;
    transition: all 200ms linear;
  }
  a:hover {
    text-decoration: none;
  }
  .link {
    color: #c4c3ca;
    text-decoration: none;
  }
  .link:hover {
    color: #ffeba7;
  }
  p {
    font-weight: 500;
    font-size: 14px;
    line-height: 1.7;
  }
  h4 {
    font-weight: 600;
    font-size: 1.5rem;
  }
  h6 {
    display: block;
    font-size: 2rem;
    font-weight: 500;
    line-height: 1.2;
    margin-top: 0;
    margin-block-start: 2.33rem;
    margin-block-end: 2.33rem;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    unicode-bidi: isolate;
  }
  h6 span {
    padding: 0 20px;
    text-transform: uppercase;
    font-weight: 700;
  }
  .full-height{
    min-height: 100vh;
  }
  [type="checkbox"]:checked,
  [type="checkbox"]:not(:checked){
    position: absolute;
    left: -9999px;
  }
  .checkbox:checked + label,
  .checkbox:not(:checked) + label{
    position: relative;
    display: block;
    text-align: center;
    width: 60px;
    height: 16px;
    border-radius: 8px;
    padding: 0;
    margin: 10px auto;
    cursor: pointer;
    background-color: #ffeba7;
  }
  .checkbox {
    display: inline-block;
    position: relative;
    cursor: pointer;
    user-select: none;
    font-size: 24px;
  }
  .checkbox:checked + label:before,
  .checkbox:not(:checked) + label:before{
    position: absolute;
    display: block;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #ffeba7;
    background-color: #102770;
    font-family: 'unicons';
    content: '\2196';
    z-index: 20;
    top: -10px;
    left: -10px;
    line-height: 36px;
    text-align: center;
    font-size: 24px;
    transition: all 0.5s ease;
  }
  .checkbox:checked + label:before {
    transform: translateX(44px) rotate(-270deg);
  }
  
  .container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
  }
  
  .col-12 {
    width: 100%;
    position: relative;
    padding-right: 15px;
    padding-left: 15px;
  }
  
  .row.full-height {
    width: 100%;
    max-width: 500px;
  }
  
  .card-3d-wrap {
    position: relative;
    width: 440px;
    max-width: 100%;
    height: 400px;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
    perspective: 800px;
    margin: 60px auto;
  }
  
  .card-3d-wrapper {
    width: 100%;
    height: 100%;
    position:absolute;    
    top: 0;
    left: 0;  
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
    transition: all 600ms ease-out; 
  }
  
  .card-front, .card-back {
    width: 100%;
    height: 100%;
    background-color: #2a2b38;
    background-image: url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/1462889/pat.svg');
    background-position: bottom center;
    background-repeat: no-repeat;
    background-size: 300%;
    position: absolute;
    border-radius: 6px;
    left: 0;
    top: 0;
    -webkit-transform-style: preserve-3d;
    transform-style: preserve-3d;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    -o-backface-visibility: hidden;
    backface-visibility: hidden;
  }
  .card-back {
    transform: rotateY(180deg);
  }
  .checkbox:checked ~ .card-3d-wrap .card-3d-wrapper {
    transform: rotateY(180deg);
  }
  .center-wrap {
    position: absolute;
    width: 100%;
    padding: 0 35px;
    top: 50%;
    left: 0;
    transform: translate3d(0, -50%, 35px) perspective(100px);
    z-index: 20;
    display: block;
  }
  
  .form-group:hover .input-icon {
    opacity: 0.8;
    transform: translateY(-50%) scale(1.1);
    transition: all 0.3s ease;
  }
  
  /* Form Classes */
  .form-group{ 
    position: relative;
    display: block;
      margin: 0;
      padding: 0;
  }
  
  .form-style {
    padding: 13px 20px;
    padding-left: 55px;
    height: 48px;
    width: 100%;
    font-weight: 500;
    border-radius: 4px;
    font-size: 14px;
    line-height: 22px;
    letter-spacing: 0.5px;
    outline: none;
    color: #c4c3ca;
    background-color: #1f2029;
    border: none;
    box-sizing: border-box;
    -webkit-transition: all 200ms linear;
    transition: all 200ms linear;
    box-shadow: 0 4px 8px 0 rgba(21,21,21,.2);
  }
  .form-style:focus,
  .form-style:active {
    border: none;
    outline: none;
    box-shadow: 0 4px 8px 0 rgba(21,21,21,.2);
  }
  
  .input-icon {
    position: absolute;
    top: 0;
    left: 18px;
    transform: translateY(-50%);
    height: 48px;
    font-size: 24px;
    line-height: 68px;
    text-align: left;
    color: #ffeba7;
    -webkit-transition: all 200ms linear;
    transition: all 200ms linear;
    display: flex;
    align-items: center;
  }
  
  .uil {
    display: inline-block;
    width: 24px;
    height: 24px;
    background-position: center;
    background-repeat: no-repeat;
    background-size: contain;
  }
  
  .form-group input:-ms-input-placeholder  {
    color: #c4c3ca;
    opacity: 0.7;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input::-moz-placeholder  {
    color: #c4c3ca;
    opacity: 0.7;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input:-moz-placeholder  {
    color: #c4c3ca;
    opacity: 0.7;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input::-webkit-input-placeholder  {
    color: #c4c3ca;
    opacity: 0.7;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input:focus:-ms-input-placeholder  {
    opacity: 0;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input:focus::-moz-placeholder  {
    opacity: 0;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input:focus:-moz-placeholder  {
    opacity: 0;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  .form-group input:focus::-webkit-input-placeholder  {
    opacity: 0;
    -webkit-transition: all 200ms linear;
      transition: all 200ms linear;
  }
  
  .btn{  
    border-radius: 4px;
    position: relative;
    height: 44px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    -webkit-transition : all 200ms linear;
    transition: all 200ms linear;
    padding: 0 30px;
    letter-spacing: 1px;
    display: -webkit-inline-flex;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-align-items: center;
    -moz-align-items: center;
    -ms-align-items: center;
    align-items: center;
    -webkit-justify-content: center;
    -moz-justify-content: center;
    -ms-justify-content: center;
    justify-content: center;
    -ms-flex-pack: center;
    text-align: center;
    border: none;
    background-color: #ffeba7;
    color: #102770;
    box-shadow: 0 8px 24px 0 rgba(255,235,167,.2);
    text-decoration: none;
  }
  .btn:active,
  .btn:focus{  
    background-color: #102770;
    color: #ffeba7;
    box-shadow: 0 8px 24px 0 rgba(16,39,112,.2);
  }
  .btn:hover{  
    background-color: #102770;
    color: #ffeba7;
    box-shadow: 0 8px 24px 0 rgba(16,39,112,.2);
  }
  .btn.running {
    transition: all 0.2s ease;
  }
  
  .text-center {
    text-align: center !important;
  }
  
  .align-self-center {
    align-self: center;
  }
  
  .justify-content-center {
    justify-content: center;
  }
  
  /* Spacing Classes */
  .py-5 {
    padding-top: 3rem;
    padding-bottom: 3rem;
  }
  
  .pb-5 {
    padding-bottom: 3rem !important;
  }
  
  .pt-5 {
    padding-top: 3rem !important;
  }
  
  .pt-sm-2 {
    @media (min-width: 576px) {
      padding-top: 0.5rem;
    }
  }
  
  .mb-0 {
    margin-bottom: 0 !important;
  }
  
  .pb-3 {
    padding-bottom: 1rem !important;
  }
  
  .mb-4 {
    margin-bottom: 1.5rem;
  }
  
  .mt-2 {
    margin-top: 0.5rem;
  }
  
  .mt-4 {
    margin-top: 1.5rem !important;
  }
  
  /* Utility Classes */
  .mx-auto {
    margin-left: auto;
    margin-right: auto;
  }
  
  /* Section Classes */
  .section {
    position: relative;
    width: 100%;
    display: block;
  }
  
  .section.text-center {
    width: 100%;
    max-width: 440px;
    margin: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  
  .logo {
    position: absolute;
    top: 30px;
    right: 30px;
    display: block;
    z-index: 100;
    transition: all 250ms linear;
  }
  .logo img {
    height: 26px;
    width: auto;
    display: block;
  }

  button:hover {
    cursor: pointer;
  }
  </style>
  
  <div class="section">
    <div class="container">
      <div class="row full-height justify-content-center">
        <div class="col-12 text-center align-self-center py-5">
          <div class="section pb-5 pt-5 pt-sm-2 text-center">
            <h6 class="mb-0 pb-3">
              <span>Log In </span>
              <span>Sign Up</span>
            </h6>
            <input class="checkbox" type="checkbox" id="reg-log" name="reg-log" />
            <label for="reg-log"></label>
            <div class="card-3d-wrap mx-auto">
              <div class="card-3d-wrapper">
                <div class="card-front">
                  <div class="center-wrap">
                    <div class="section text-center">
                      <h4 class="mb-4 pb-3">Log In</h4>
                      <form method="POST" action="">
                        <input type="hidden" name="action" value="login">
                        <div class="form-group">
                            <input type="email" name="logemail" class="form-style" placeholder="Your Email" id="logemail" autocomplete="off" required>
                            <i class="input-icon uil uil-at"></i>
                        </div>
                        <div class="form-group mt-2">
                            <input type="password" name="logpass" class="form-style" placeholder="Your Password" id="logpass" autocomplete="off" required>
                            <i class="input-icon uil uil-lock-alt"></i>
                        </div>
                        <button class="btn mt-4" type="submit">submit</button>
                        <p class="mb-0 mt-4 text-center"><a href="#0" class="link">Forgot your password?</a></p>
                      </form>
                    </div>
                  </div>
                </div>
                <div class="card-back">
                  <div class="center-wrap">
                    <div class="section text-center">
                      <h4 class="mb-4 pb-3">Sign Up</h4>
                      <form method="POST" action="">
                        <input type="hidden" name="action" value="signup">
                        <div class="form-group">
                        <input type="text" name="regname" class="form-style" placeholder="Your Full Name" id="regname" autocomplete="off" required>
                        <i class="input-icon uil uil-user"></i>
                      </div>
                      <div class="form-group mt-2">
                        <input type="email" name="regemail" class="form-style" placeholder="Your Email" id="regemail" autocomplete="off" required>
                        <i class="input-icon uil uil-at"></i>
                      </div>
                      <div class="form-group mt-2">
                        <input type="password" name="regpass" class="form-style" placeholder="Your Password" id="regpass" autocomplete="off" required>
                        <i class="input-icon uil uil-lock-alt"></i>
                      </div>
                      <button class="btn mt-4" type="submit">submit</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      class ButtonAvoidance {
        constructor() {
          this.currentForm = 'login';
          this.isRunning = false;
          this.currentOffsetX = 0;
          this.currentOffsetY = 0;
          this.maxOffset = 150;
          this.easing = 0.2;
          
          this.loginButton = document.querySelector('.card-front .btn');
          this.signupButton = document.querySelector('.card-back .btn');
          this.checkbox = document.querySelector('.checkbox');
          this.activeButton = this.loginButton;
          
          this.init();
        }
        
        init() {
          this.checkbox.addEventListener("change", () => {
            this.activeButton = this.checkbox.checked ? this.signupButton : this.loginButton;
            this.resetButtonPosition();
            this.checkFormCompletion();
          });
          
          document.querySelectorAll(".form-style").forEach(input => {
            input.addEventListener("input", () => this.checkFormCompletion());
          });
          
          document.addEventListener("mousemove", (e) => this.handleMouseMove(e));
          requestAnimationFrame(() => this.animate());
          this.checkFormCompletion();
        }
        
        resetButtonPosition() {
          this.currentOffsetX = 0;
          this.currentOffsetY = 0;
          this.activeButton.style.transform = "translate(0, 0)";
          this.isRunning = false;
        }
        
        checkFormCompletion() {
          const activeForm = this.activeButton.closest(".card-front") ? ".card-front" : ".card-back";
          const inputs = document.querySelector(activeForm).querySelectorAll(".form-style");
          this.isRunning = !Array.from(inputs).every(input => input.value.trim() !== "");
        }
        
        handleMouseMove(e) {
          if (!this.isRunning) return;
          const rect = this.activeButton.getBoundingClientRect();
          const dx = rect.left + rect.width / 2 - e.clientX;
          const dy = rect.top + rect.height / 2 - e.clientY;
          const distance = Math.sqrt(dx * dx + dy * dy);
          
          if (distance < 100) {
            const moveX = (dx / distance) * this.maxOffset;
            const moveY = (dy / distance) * this.maxOffset;
            this.currentOffsetX += (moveX - this.currentOffsetX) * this.easing;
            this.currentOffsetY += (moveY - this.currentOffsetY) * this.easing;
          }
        }
        
        animate() {
          if (this.isRunning) {
            this.activeButton.style.transform = `translate(${this.currentOffsetX}px, ${this.currentOffsetY}px)`;
          }
          requestAnimationFrame(() => this.animate());
        }
      }
      
      new ButtonAvoidance();
    });
  </script>
</body>
</html>