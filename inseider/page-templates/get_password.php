<?php
/**
 * Template Name: passwort bekommen
 */


add_action('genesis_before_header', 'remove_header');
add_action('genesis_before_header', 'hide_push_popup');

//todo configure
function hide_push_popup()
{
    echo '<style>
    #onesignal-slidedown-container{
    display: none;
    }
</style>';
}


add_action('genesis_before_header', 'get_password');

//todo configure
function get_password()
{
?>
    <div class="site-inner">
    <div id="password-lost-form" class="widecolumn">
    <br>
    <br>
    <h3>Passwort zurücksetzen</h3>
    <p>Bitte geben Sie Ihre E-Mail Adresse ein, wir werden Ihnen dann einen Link schicken.<br>Mit diesem können Sie Ihr
        Passwort zurücksetzen.</p>

    <form id="lostpasswordform" action="<?php echo wp_lostpassword_url(); ?>" method="post">
        <p class="form-row">
            <label for="user_login">E-Mail
                <input type="text" autocomplete="off" name="user_login" id="user_login">
        </p>

        <div class="flex_button_container lostpassword-submit">
            <input type="submit" name="submit" class=" spk_wide_button flex_button lostpassword-button"
                   value="<?php _e('Passwort zurücksetzen', 'personalize-login'); ?>"/>
            <a href="/member-login" class="button spk_wide_button flex_button secondary_button">zurück</a>
        </div>
    </form>
</div>
    </div>

<?php
}

genesis();
