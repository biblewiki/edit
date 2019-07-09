<!DOCTYPE html>
<html lang="de">

<head>
    <?php echo file_get_contents('html/head.html'); ?>

    <title>Profil | BibleWiki</title>
</head>

<body>
    <!-- Navbar -->
    <?php echo file_get_contents('html/navbar.html'); ?>

    <main id="main">

            <!-- Sidebar -->
            <?php echo file_get_contents('html/sidebar.html'); ?>

            <!-- Content -->
            <div class="content">
                <h1 class="capitalize">Profil</h1>
                <div class="content-post profile">
                    <form class="profile-form">
                        <div class="input-item username">
                            <input type="text" value="michi" disabled/>
                            <div class="input-name name-small">Benutzername</div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Deinen Benutzernamen kannst du nicht ändern.</span>
                            </div>
                        </div>
                        <div class="input-item firstname">
                            <input type="text" name="firstname" required/>
                            <div class="input-name">Vorname</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Dein Vorname wird oben recht angezeigt.</span>
                            </div>
                        </div>
                        <div class="input-item secondname">
                            <input type="text" name="lastname" required/>
                            <div class="input-name">Nachname</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Dein Nachname und Vorname erscheint überall.</span>
                            </div>
                        </div>
                        <div class="input-item email-or-tel noselect">
                            <div class="input-name name-small">Benachrichtigung</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="button-email-tel button-email active">
                                <span>Email</span>
                            </div>
                            <div class="button-email-tel button-tel">
                                <span>Telegram</span>
                            </div>
                        </div>
                        <div class="input-item email">
                            <input type="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" required/>
                            <div class="input-name">Email</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-exclamation"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Via deine Email wirst du benachrichtigt und kannst dich anmelden.</span>
                            </div>
                        </div>
                        <div class="input-item tel">
                            <input type="tel" pattern="[0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}|[0-9]{10}" required/>
                            <div class="input-name">Natelnummer</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-exclamation"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Wenn du deine Nummer angibts kannst du via denn Messenger Telegram benachrichtigt werden und kannst dich auch damit anmelden.</span>
                            </div>
                        </div>
                        <div class="input-item password">
                            <input type="password" pattern=".{6,}" data-size="10" data-character="a-z,A-Z,0-9,#" required/>
                            <div class="input-name">Passwort</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-visible">
                                <i class="fas fa-eye"></i>
                                <i class="fas fa-eye-slash" hidden></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Dein momentanes Passwort wird nicht angezeigt. Gebrauche mindestens 6 Zeichen.</span>
                            </div>
                        </div>
                        <div class="input-item password-change">
                            <div class="button-area noselect">
                                <span>Neues Passwort generieren</span>
                            </div>
                        </div>
                        <div class="input-item test">
                            <input type="text" name="test" required/>
                            <div class="input-name">Test</div>
                            <div class="input-status">
                                <i class="fas fa-check"></i>
                                <i class="fas fa-times"></i>
                            </div>
                            <div class="input-info">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="input-source">
                                <i class="fas fa-bookmark"></i>
                            </div>
                            <div class="input-info-text">
                                <span>Dies ist ein Test.</span>
                            </div>
                        </div>
                        <?php echo file_get_contents('html/bottombar_profile.html'); ?>
                    </form>
                </div>
            </div>
            <!-- END Content -->

    </main>

    <?php echo file_get_contents('html/script.html'); ?>
</body>

</html>