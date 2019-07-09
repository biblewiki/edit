<!DOCTYPE html>
<html lang="de">

<head>
    <?php echo file_get_contents('html/head.html'); ?>

    <title>Übersicht | BibleWiki</title>
</head>

<body>
    <!-- Navbar -->
    <?php echo file_get_contents('html/navbar.html'); ?>

    <main id="main">

            <!-- Sidebar -->
            <?php echo file_get_contents('html/sidebar.html'); ?>

            <!-- Content -->
            <div class="content">
                <h1 class="capitalize">Übersicht</h1>
                <div class="content-post">
                    <div class="content-statistics">
                        <h2 class="capitalize">Statistiken</h2>
                        <div class="content-diagram">
                            <div class="diagram-lines">
                                <div class="diagram-bar">
                                    50%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-team">
                        <h2 class="capitalize">Team</h2>
                        <ul class="content-persons">
                            <li class="team-person">
                                <span>Michael</span>
                            </li>
                            <li class="team-person">
                                <span>Jonathan</span>
                            </li>
                            <li class="team-person">
                                <span>Joel</span>
                            </li>
                            <li class="team-person">
                                <span>Fabian</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- END Content -->

    </main>

    <?php echo file_get_contents('html/script.html'); ?>
</body>

</html>