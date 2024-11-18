<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Login</title>
</head>

<body>
    <section>
        <div class="login-box">
            <form action="loginController.php" method="POST">
                <h2>PPH21 App</h2>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person-outline"></ion-icon>
                    </span>
                    <input type="text" name="username" required="required">
                    <label>User Name</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                    </span>
                    <input type="password" name="password" required="required">
                    <label>Password</label>
                </div>
                <input class="login" type="submit" value="Login">
                <!--Menampung jika ada pesan-->
                <?php
                if (isset($_GET['pesan'])) {
                    echo '<script>alert("' . $_GET['pesan'] . '")</script>';
                }  ?>
            </form>
        </div>
    </section>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>