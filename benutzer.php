<!DOCTYPE html>
<html lang="de">

<head>
    <?php echo file_get_contents('html/head.html'); ?>

    <title>Benutzer | BibleWiki</title>
</head>

<body>
    <!-- Navbar -->
    <?php echo file_get_contents('html/navbar.html'); ?>

    <main id="main">

            <!-- Sidebar -->
            <?php echo file_get_contents('html/sidebar.html'); ?>

            <!-- Content -->
            <div class="content">
                <h1 class="capitalize">Benutzer</h1>
                <div class="content-post profiles">
                    profiles
                </div>
                <?php echo file_get_contents('html/bottombar.html'); ?>
            </div>
            <!-- END Content -->

    </main>

    <?php echo file_get_contents('html/script.html'); ?>
</body>

</html>