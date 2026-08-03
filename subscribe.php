<?php
require_once "admin/config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subscribe</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:#f4f6f9;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.card{
    width:400px;
    background:#fff;
    padding:25px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,.15);
}

h2{
    text-align:center;
    margin-bottom:20px;
    color:#0d6efd;
}

label{
    display:block;
    margin-bottom:5px;
    font-weight:bold;
}

select,
input{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:6px;
}

button{
    width:100%;
    padding:10px;
    border:none;
    border-radius:6px;
    background:#0d6efd;
    color:#fff;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#0b5ed7;
}

</style>

</head>
<body>

<div class="card">

<h2>Email Subscription</h2>

<form action="admin/controllers/subscriber.php" method="POST">

<label>Location Phase</label>

<select name="phase" required>
    <option value="">-- Select Phase --</option>
    <option value="Phase 1">Phase 1</option>
    <option value="Phase 2">Phase 2</option>
    <option value="Phase 3">Phase 3</option>
    <option value="Phase 4">Phase 4</option>
    <option value="Phase 5">Phase 5</option>
    <option value="Phase 6">Phase 6</option>
</select>

<label>Email Address</label>

<input
type="email"
name="email"
placeholder="example@email.com"
required>

<button type="submit">
Subscribe
</button>

</form>

</div>

</body>
</html>