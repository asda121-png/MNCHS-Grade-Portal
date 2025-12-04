<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MNCHS Grade Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; height: 100vh; display: flex; align-items: center; }
        .card { max-width: 420px; margin: auto; }
        .logo { width: 70px; height: 70px; background: #0d6efd; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 28px; margin: 0 auto 1rem; }
    </style>
</head>
<body>
<div class="card p-4 shadow">
    <div class="text-center">
        <div ><img src="logo.png" style="height: 9vh; width:7vw;"></div><br>
        <h4>MNCHS Grade Portal</h4>
    </div>

    <?php if ($login_err): ?>
        <div class="alert alert-danger"><?= $login_err ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Username or Email</label>
            <input type="text" name="input" class="form-control <?=!empty($input_err)?'is-invalid':''?>"
                   value="<?= htmlspecialchars($_POST['input']??'') ?>">
            <div class="invalid-feedback"><?= $input_err ?></div>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control <?=!empty($password_err)?'is-invalid':''?>">
            <div class="invalid-feedback"><?= $password_err ?></div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
        <small class="text-muted">
            <strong>Admin:</strong> admin@email.com / Admin123<br>
            <strong>Teacher:</strong> tmary / password123<br>
            <strong>Student:</strong> s67890 / password123<br>
            <strong>Parent:</strong> p67890 / password123
        </small>
    </div>
</div>
</body>
</html>