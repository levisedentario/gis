<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In</title>
<style>
  :root {
    --black: #0a0a0a;
    --white: #ffffff;
    --gray-light: #e8e8e8;
    --gray-mid: #9a9a9a;
    --gray-dark: #2b2b2b;
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--white);
    background-image:
      linear-gradient(var(--gray-light) 1px, transparent 1px),
      linear-gradient(90deg, var(--gray-light) 1px, transparent 1px);
    background-size: 40px 40px;
    font-family: 'Helvetica Neue', Arial, sans-serif;
    padding: 24px;
  }

  .card {
    width: 100%;
    max-width: 360px;
    background: var(--white);
    border: 1.5px solid var(--black);
    padding: 48px 36px 40px;
  }

  .logo {
    display: flex;
    justify-content: center;
    margin-bottom: 28px;
  }

  .logo svg {
    width: 56px;
    height: 56px;
  }

  h1 {
    text-align: center;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--black);
    margin-bottom: 36px;
  }

  .field {
    margin-bottom: 22px;
  }

  label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--gray-dark);
    margin-bottom: 8px;
  }

  input {
    width: 100%;
    border: none;
    border-bottom: 1.5px solid var(--black);
    background: transparent;
    padding: 10px 2px;
    font-size: 15px;
    color: var(--black);
    outline: none;
    transition: border-color 0.15s ease;
  }

  input::placeholder {
    color: var(--gray-mid);
  }

  input:focus {
    border-bottom-width: 2px;
  }

  .submit {
    width: 100%;
    margin-top: 30px;
    padding: 13px;
    background: var(--black);
    color: var(--white);
    border: 1.5px solid var(--black);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
  }

  .submit:hover {
    background: var(--white);
    color: var(--black);
  }

  .submit:focus-visible,
  input:focus-visible {
    outline: 2px solid var(--black);
    outline-offset: 2px;
  }

  .error-message {
    margin-top: 16px;
    color: #b00020;
    font-size: 13px;
    text-align: center;
  }
</style>
</head>
<body>

  <div class="card">
    <div class="logo">
      <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="1" y="1" width="54" height="54" stroke="black" stroke-width="1.5"/>
        <path d="M28 14 L40 40 H32.5 L28 30 L23.5 40 H16 L28 14Z" fill="black"/>
      </svg>
    </div>

    <h1>Sign In</h1>

    <form action="admin/controllers/loginController.php" method="POST">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" autocomplete="username" required>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
      </div>

      <button type="submit" class="submit">Sign In</button>
    </form>

    <?php if (isset($_SESSION['error'])): ?>
      <p class="error-message">
        <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>
      </p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
  </div>

</body>
</html>

