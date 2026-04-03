<?php
// Template Name: Change Password
handle_password_lost_during_changing();
$response = change_password();

function handle_password_lost_during_changing()
{
    if ($_POST['submit'] == 'submit-pw-lost-during-changing') {
        wp_destroy_all_sessions();
        wp_safe_redirect(home_url('member-password-lost'));
        exit();
    }
}

add_action('genesis_before_header', 'remove_header_if_forced');

function remove_header_if_forced()
{

    if (!$_GET['header'] || $_GET['header'] !== 'true') {
        remove_header();
    }
}


get_header();


if (have_posts()): while (have_posts()): the_post(); ?>

    <style>
        #onesignal-slidedown-container {
            display: none;
        }
    </style>

    <div class="container" style="width: 80%; margin: 0 auto;">
        <?php
        if ($_GET['header'] === 'true') {
            ?>
            <div class='post-top-bar'><a class="noHighlight" href="/"><p id='inseider-back-Button'>
                        <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück</p></a>
            </div>
            <?php
        }
        ?>

        <h1><?php the_title(); ?></h1>
        <?php
        $pw_policy = 'Passwörter müssen mindestens 8 Zeichen lang sein und mindestens ein Sonderzeichen, eine Zahl, einen Großbuchstaben und einen Kleinbuchstaben enthalten.';

        if ($_GET['expass'] == 'expired') {
            echo '<p>Ihr Passwort ist abgelaufen und muss geändert werden, bevor Sie fortfahren können. <br> ' . $pw_policy . '</p>';
        } else {
            echo '<p>' . $pw_policy . '</p>';
        }


        the_content(); ?>

        <?php
        if ($response !== true && !empty($response)) {
            echo '<div id="server-errors" class="error_message">';
            echo '<h3>Fehler:</h3>';
            foreach ($response as $error) {
                echo '<p>';
                echo "<strong>$error</strong>";
                echo '</p>';
            }
            echo '</div>';
        }
        global $current_user;
        ?>

        <div id="client-errors" class="error_message" style="display: none">
            <p id="client-error-newAndOldPwEqual" style="display: none">Das neue Passwort muss sich vom alten
                unterscheiden.</p>
            <p id="client-error-newAndConfirmNewPwDifferent" style="display: none">Die beiden Felder für das neue
                Passwort stimmen nicht überein.</p>
        </div>
        <br>
        <form action="" method="post">
            <?php wp_nonce_field('wps_change_passw'); ?>
            <input style="display: none" type="text" name="username" autocomplete="username"
                   value="<?php echo $current_user->user_login ?>">
            <label for="current_password">Aktuelles Passwort:</label>
            <input id="current_password" autocomplete="current-password" type="password" name="current_password"
                   title="current_password"
                   placeholder="">
            <a class="button show_hide_password" style="float: right"
               id="show_hide_current_password"><?php echo file_get_contents(get_stylesheet_directory() . "/images/" . "/eye-icon.svg", false, null) ?></a>
            <label for="new_password">Neues Passwort:</label>
            <input id="new_password" type="password" autocomplete="new-password" name="new_password"
                   title="new_password" placeholder="">
            <a class="button show_hide_password" style="float: right"
               id="show_hide_new_password"><?php echo file_get_contents(get_stylesheet_directory() . "/images/" . "/eye-icon.svg", false, null) ?></a>

            <label for="confirm_new_password">Neues Passwort bestätigen:</label>
            <input id="confirm_new_password" type="password" autocomplete="new-password" name="confirm_new_password"
                   title="confirm_new_password"
                   placeholder="">
            <a class="button show_hide_password" style="float: right"
               id="show_hide_confirm_new_password"><?php echo file_get_contents(get_stylesheet_directory() . "/images/" . "/eye-icon.svg", false, null) ?></a>


            <button class="spk_wide_button" name="submit" style="margin-top: 1em" value="submit-new-pw" type="submit">
                Passwort ändern
            </button>
            <button class="forgot-password-link" name="submit" value="submit-pw-lost-during-changing" type="submit">
                Passwort zurücksetzen
            </button>
        </form>

        <script>
            (function ($) {
                $(document).ready(function () {
                    var newAndOldPwEqual = false;
                    var newAndconfirmNewPwDifferent = false;

                    var current_password = $('#current_password');
                    var confirm_new_password = $('#confirm_new_password');
                    var new_password = $('#new_password');

                    var client_errors = $('#client-errors');
                    var client_error_newAndOldPwEqual = $('#client-error-newAndOldPwEqual');
                    var client_error_newAndConfirmNewPwDifferent = $('#client-error-newAndConfirmNewPwDifferent');


                    current_password.change(function () {
                        check_new_pw();
                    });

                    confirm_new_password.change(function () {
                        check_confirm_new_pw()
                    });

                    new_password.change(function () {
                        check_new_pw();
                        check_confirm_new_pw()
                    });


                    bind_show_hide_password(jQuery('#show_hide_current_password'), jQuery('#current_password'));
                    bind_show_hide_password(jQuery('#show_hide_new_password'), jQuery('#new_password'));
                    bind_show_hide_password(jQuery('#show_hide_confirm_new_password'), jQuery('#confirm_new_password'));


                    function bind_show_hide_password(control, field) {
                        control.bind('click', function () {
                            if (control.hasClass('active')) {
                                field.attr('type', 'password');
                                control.removeClass('active');
                            } else {
                                field.attr('type', 'text');
                                control.addClass('active');
                            }
                        })
                    }

                    function check_new_pw() {
                        if (new_password.val() === current_password.val() && current_password.val() !== '') {
                            newAndOldPwEqual = true;
                            client_errors.show();
                            client_error_newAndOldPwEqual.show();
                        } else {
                            newAndOldPwEqual = false;
                            client_error_newAndOldPwEqual.hide();
                            if (!newAndconfirmNewPwDifferent) {
                                client_errors.hide();
                            }
                        }
                    }

                    function check_confirm_new_pw() {
                        if (new_password.val() !== confirm_new_password.val() && confirm_new_password.val() !== '') {
                            newAndconfirmNewPwDifferent = true;
                            client_errors.show();
                            client_error_newAndConfirmNewPwDifferent.show();
                        } else {

                            newAndconfirmNewPwDifferent = false;
                            client_error_newAndConfirmNewPwDifferent.hide();
                            if (!newAndOldPwEqual) {
                                client_errors.hide();
                            }
                        }
                    }

                });
            })(jQuery);
        </script>

    </div>
<?php endwhile; endif; ?>

<?php get_footer(); ?>