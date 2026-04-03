<?php

/**
 * Template Name: Front End User Management
 */


global $current_user;
handle_user_management_restrictions($current_user);


add_action('genesis_before_content', 'add_back');

function add_back()
{
    ?>
    <div class='post-top-bar'><a class="noHighlight" href="/"><p id='inseider-back-Button'>
                <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück</p></a>
    </div>
    <?php
}


add_action('genesis_loop', 'user_management_loop');
function user_management_loop()
{

    $form_result = handle_user_management_form_submit();

    if (is_array($form_result) && !empty($form_result)) {
        echo '<div class="error_message">';
        echo '<h3>Fehler:</h3>';
        foreach ($form_result as $error) {
            echo '<p>';
            echo "<strong>$error</strong>";
            echo '</p>';
        }
        echo '</div>';
    }

    if ($form_result !== true) {
        ?>
        <div class="postbox visible_postbox">

            <h2>Einzelne Benutzer erstellen</h2>
            <form id="user_creating" name="user_creating" method="post" autocomplete="off">

                <?php wp_nonce_field('inseider-create-user', 'inseider-create-user-nonce'); ?>


                <label for="pNumber_Create"><?php echo $GLOBALS['Strings']['User_Management_username_label']; ?></label>
                <input type="text" name="pNumber_Create" id="pNumber_Create" autocomplete="new-password">

                <label for="eMail_Create">E-Mail</label>
                <input type="text" name="eMail_Create" id="eMail_Create" autocomplete="new-password">

                <label for="firstname_Create">Vorname</label>
                <input type="text" name="firstname_Create" id="firstname_Create">

                <label for="lastname_Create">Nachname</label>
                <input type="text" name="lastname_Create" id="lastname_Create">

                <label for="role_Create">Rolle</label>
                <select id="role_Create" name="role_Create">
                    <?php echo get_role_create_select_options(); ?>
                </select>


                <br>
                <br>
                <button class="spk_wide_button marginTopBottom" name="submit" value="createUser" type="submit">Nutzer
                    erstellen
                </button>
            </form>
        </div>

        <datalist id="userList_edit">
            <?php
            get_users_datalist_options();
            ?>
        </datalist>

        <div class="postbox visible_postbox">
            <h2>Einzelne Benutzer bearbeiten</h2>
            <form id="user_editing" name="user_editing" method="post" autocomplete="off">
                <?php wp_nonce_field('inseider-edit-user', 'inseider-edit-user-nonce'); ?>


                <label><?php echo $GLOBALS['Strings']['User_Management_login_label']; ?></label>
                <input type="text" autocomplete="list" name="pNumber_Edit" id="pNumber_Edit" list="userList_edit">



                <p id="pNumber_Edit_hint" style="display:none;margin-bottom: 0;color: #f00;">Enter um zu bestätigen.</p>


                <div id="eMail_Edit_container" style="display: none">
                    <label for="eMail_Edit">E-Mail</label>
                    <input type="text" name="eMail_Edit" id="eMail_Edit" autocomplete="new-password">
                </div>


                <div id="firstname_Edit_container" style="display: none">
                    <label for="firstname_Edit">Vorname</label>
                    <input type="text" name="firstname_Edit" id="firstname_Edit">
                </div>


                <div id="lastname_Edit_container" style="display: none">
                    <label for="lastname_Edit">Nachname</label>
                    <input type="text" name="lastname_Edit" id="lastname_Edit">
                </div>

                <div id="role_Edit_container" style="display: none">
                    <label for="role_Edit">Rolle</label>
                    <select name="role_Edit" id="role_Edit">
                        <?php echo get_role_create_select_options(); ?>
                    </select>
                </div>

                <br>
                <button class="spk_wide_button marginTopBottom" name="submit" id="editUser-submit" style="display: none"
                        value="editUser" type="submit">Nutzer bearbeiten
                </button>
            </form>
            <br>
            <button class="spk_wide_button" id="edit_dummy-search-button"
                    type="submit">suchen
            </button>
        </div>

        <div class="postbox visible_postbox">
            <h2>Einzelne Benutzer löschen</h2>
            <form id="user_deleting" name="user_deleting" method="post" autocomplete="off">

                <?php wp_nonce_field('inseider-delete-user', 'inseider-delete-user-nonce'); ?>

                <label for="toDelete"><?php echo $GLOBALS['Strings']['User_Management_login_label']; ?></label>
                <input type="text" name="toDelete" id="toDelete" list="userList_edit">

                <p id="user_delete_error" style="display: none;color: #f00">
                </p>
                <table id="user_delete_data" style="display:none;width:100%">
                    <tr>
                        <td>E-Mail</td>
                        <td id="user_delete_data_email"></td>
                    </tr>
                    <tr>
                        <td>Vorname</td>
                        <td id="user_delete_data_firstname"></td>
                    </tr>
                    <tr>
                        <td>Nachname</td>
                        <td id="user_delete_data_lastname"></td>
                    </tr>
                    <tr>
                        <td>Rolle</td>
                        <td id="user_delete_data_role"></td>
                    </tr>
                </table>
                <button class="spk_wide_button" id="delete_submit" style="display: none" name="submit"
                        value="deleteUser" type="submit">Nutzer Löschen
                </button>
            </form>
            <br>
            <button class="spk_wide_button" id="delete_dummy-search-button">suchen</button>
        </div>

        <div class="postbox visible_postbox">
            <h2>Mehrere Benutzer erstellen</h2>
            <form id="bulk_user_creating" name="bulk_user_creating" method="post" autocomplete="off">

                <?php wp_nonce_field('inseider-bulk-user-create', 'inseider-bulk-user-create-nonce'); ?>

                <label for="bulkCreateInput">Bitte verwenden Sie folgende Tabelle als Vorlage und übertragen Sie der
                    Benutzerdaten per Copy&Paste:</label>
                <a href="/wp-content/themes/inseider/assets/insider_mehrere_Benutzer_erstellen.xls">Excel-Tabelle</a>
                <br>
                <br>
                <textarea oninput="auto_grow(this)" id="bulkCreateInput" tabindex="3" name="bulkCreateInput" cols="50"
                          rows="6"
                          placeholder="Entsprechende Zeilen aus Excel kopieren und hier einfügen"></textarea>
                <br>
                <br>
                <button class="spk_wide_button" name="submit" value="bulkCreateUsers" type="submit">Nutzer erstellen
                </button>
            </form>
        </div>

        <div class="postbox visible_postbox">
            <h2>Liste aller Nutzer anzeigen</h2>
            <form id="list_all_users" name="list_all_users" method="post" autocomplete="off">
                <button class="spk_wide_button" name="submit" value="list_all_users" type="submit">Liste anzeigen
                </button>
            </form>
        </div>

        <?php
        form_js();
        ?>
        <script>

            window.addEventListener('load', function () {
                jQuery(document).on("keydown", "#user_editing", function (event) {
                    if (event.key === "Enter") {
                        jQuery(':focus').blur();
                        return false;
                    }
                    return true;
                });

                jQuery('#pNumber_Edit').on('change', function () {

                    var user_login = this.value;
                    var str = '&action=edit_user&user=' + user_login;

                    jQuery.ajax({
                        type: "POST",
                        dataType: "json",
                        url: the_ajax_script.ajaxurl,
                        data: str,
                        success: function (rawdata) {
                            if (rawdata === 'false') {
                                //force reload, this will identify user or redirect him to login
                                location.reload();
                            } else {
                                //var fulfilled = jQuery.parseJSON(rawdata);
                                var fulfilled = rawdata;

                                if (fulfilled.user_email) {
                                    jQuery('#eMail_Edit').val(fulfilled.user_email);
                                    jQuery('#firstname_Edit').val(fulfilled.first_name);
                                    jQuery('#lastname_Edit').val(fulfilled.last_name);
                                    jQuery('#role_Edit').val(fulfilled.role);

                                    jQuery('#eMail_Edit_container').css("display", "block");
                                    jQuery('#firstname_Edit_container').css("display", "block");
                                    jQuery('#lastname_Edit_container').css("display", "block");
                                    jQuery('#role_Edit_container').css("display", "block");
                                    jQuery('#editUser-submit').css("display", "block");
                                    jQuery('#edit_dummy-search-button').css("display", "none");

                                    //jQuery('#pNumber_Edit_hint').css("display", "none");

                                } else {
                                    jQuery('#eMail_Edit_container').css("display", "none");
                                    jQuery('#firstname_Edit_container').css("display", "none");
                                    jQuery('#lastname_Edit_container').css("display", "none");
                                    jQuery('#role_Edit_container').css("display", "none");
                                    jQuery('#editUser-submit').css("display", "none");
                                    jQuery('#edit_dummy-search-button').css("display", "block");
                                    jQuery('#pNumber_Edit_hint').css("display", "block");
                                    jQuery('#pNumber_Edit_hint').text('Kein Nutzer gefunden');
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error('ERROR ' + jqXHR + textStatus + errorThrown);
                            location.reload();
                        }
                    });
                });

                jQuery('#toDelete').on('change', function () {

                    var user_login = this.value;
                    var str = '&action=edit_user&user=' + user_login;

                    jQuery.ajax({
                        type: "POST",
                        dataType: "html",
                        url: the_ajax_script.ajaxurl,
                        data: str,
                        success: function (rawdata) {
                            if (rawdata === 'false') {
                                //force reload, this will identify user or redirect him to login
                                location.reload();
                            } else {
                                var fulfilled = jQuery.parseJSON(rawdata);

                                if (fulfilled.user_email) {

                                    jQuery('#user_delete_data_email').text(fulfilled.user_email);
                                    jQuery('#user_delete_data_firstname').text(fulfilled.first_name);
                                    jQuery('#user_delete_data_lastname').text(fulfilled.last_name);

                                    jQuery('#user_delete_data_role').text(fulfilled.role_name);

                                    jQuery('#delete_submit').css("display", "block");
                                    jQuery('#delete_dummy-search-button').css("display", "none");

                                    jQuery('#user_delete_data').css("display", "block");
                                    jQuery('#user_delete_error').css("display", "none");

                                } else {
                                    jQuery('#user_delete_data').css("display", "none");
                                    jQuery('#user_delete_error').css("display", "block");
                                    jQuery('#user_delete_error').html('Kein Nutzer gefunden');

                                    jQuery('#delete_submit').css("display", "none");
                                    jQuery('#delete_dummy-search-button').css("display", "block");
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error('ERROR ' + jqXHR + textStatus + errorThrown);
                            location.reload();
                        }
                    })
                    ;
                });

            });

        </script>
        <?php
    } else {

        echo '<a href="' . home_url('user-management') . '"><button class="spk_wide_button">zurück</button></a>';

        form_js();
    }
    wp_reset_postdata();
}


function handle_user_management_form_submit()
{
    $errors = array();
    if (!current_user_can('delete_users')) {
        wp_safe_redirect(home_url());
        exit();
    }
    if ($_POST['submit'] == 'createUser') {

        if (!wp_verify_nonce($_POST['inseider-create-user-nonce'], 'inseider-create-user')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $login_name = sanitize_text_field($_POST['pNumber_Create']);
        $email = sanitize_email($_POST['eMail_Create']);
        $firstname = sanitize_text_field($_POST['firstname_Create']);
        $lastname = sanitize_text_field($_POST['lastname_Create']);
        $role = sanitize_text_field($_POST['role_Create']);

        if($login_name==null || empty($login_name)){
            $errors[] = 'Bitte geben Sie eine Personalnummer ein.';
        }

        if($email==null || empty($email)){
            $errors[] = 'Bitte geben Sie eine E-Mail-Adresse ein.';
        }

        if($firstname==null || empty($firstname)){
            $errors[] = 'Bitte geben Sie einen Vornamen ein.';
        }

        if($lastname==null ||empty($lastname)){
            $errors[] = 'Bitte geben Sie einen Nachnamen ein.';
        }

        if($role==null ||empty($role)){
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
        }

        if(!empty($errors)){
            return $errors;
        }


        $user_login = get_user_by('login',$login_name);
        $user_mail = get_user_by_email($email);
        $user = $user_login ? $user_login : $user_mail;

        if ($user) {
            $errors[] = 'Der Nutzer mit dieser Personalnummer oder E-Mail-Adresse existiert bereits:<br>
                    <span class="review-information">Personalnummer: ' . $user->user_login . '</span><br>
                    <span class="review-information">E-Mail-Adresse: ' . $user->user_email . '</span>';
            return $errors;
        } else {

            global $wp_roles;
            $roles_display_name = translate_user_role($wp_roles->roles[$role]['name']);

            echo '<h1> Sind Sie sicher?</h1> <span>Sie sind im Begriff, folgenden Nutzer zu erstellen:</span>';
            echo '<div class="review-information"><p>Personalnummer: ' . $login_name . '<br>';
            echo 'Name: ' . $firstname . ' ' . $lastname . '<br>';
            echo 'E-Mail: ' . $email . '<br>';
            echo 'Rolle: ' . $roles_display_name . '</p></div>';


            ?>
            <div id="final_user_creating_form">
            <form id="final_user_creating" name="final_user_creating" method="post" enctype="multipart/form-data">

                <?php wp_nonce_field('inseider-final-create-user','inseider-final-create-user-nonce'); ?>

                <input type="hidden" name="pNumber_FinalCreate" id="pNumber_FinalCreate"
                       value="<?php echo $login_name; ?>">
                <input type="hidden" name="eMail_FinalCreate" id="eMail_FinalCreate" value="<?php echo $email; ?>">
                <input type="hidden" name="firstname_FinalCreate" id="firstname_FinalCreate"
                       value="<?php echo $firstname; ?>">
                <input type="hidden" name="lastname_FinalCreate" id="lastname_FinalCreate"
                       value="<?php echo $lastname; ?>">
                <input type="hidden" name="role_FinalCreate" id="role_FinalCreate" value="<?php echo $role; ?>">

                <?php
                if ($role == 'business_partner') {

                    ?>
                   <label for="boni_post_id">Post ID</label>
                    <input type="text" name="boni_post_id" id="boni_post_id">
                    <br>
                    <br>
                    <?php
/*                    */?><!--
                    <div id="thumbnailCont">
                        <label id="thumbnail_label" for="thumbnail">Vorschaubild für Boni-Post:</label>
                        <div class="uploadBox">
                            <div id="thumbnail_preview_images"></div>
                            <input type="file" class="accepts_drops" name="thumbnail" id="thumbnail"
                                   accept="image/png, image/jpeg">
                            <p id="thumbnailUploadText"> Bitte legen Sie das Vorschaubild hier ab oder klicken Sie auf
                                diesen Bereich.<br>
                                Empfohlene Größe: 1920px x 1080px (Querformat, 16:9)</p>
                        </div>
                    </div>
                    --><?php
                }
                ?>
                <button class="spk_wide_button" name="submit" value="finalCreateUser" type="submit">Nutzer
                    erstellen
                </button>
            </form>
            </div>


            <?php

            return true;
        }
    }
    else if ($_POST['submit'] == 'editUser') {

        if (!wp_verify_nonce($_POST['inseider-edit-user-nonce'], 'inseider-edit-user')) {
            $errors[] =  'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $login_name = sanitize_text_field($_POST['pNumber_Edit']);
        $email = sanitize_email($_POST['eMail_Edit']);
        $firstname = sanitize_text_field($_POST['firstname_Edit']);
        $lastname = sanitize_text_field($_POST['lastname_Edit']);
        $role = sanitize_text_field($_POST['role_Edit']);


        if ($login_name != null) {

            $user = get_user_by('login',$login_name);

            $user_mail = get_user_by_email($email);


            if (!$user) {
                $errors[] =  'Der Nutzer mit dieser Personalnummer existiert nicht: <span class="review-information">' . $login_name . '</span>';
                return $errors;
            } else if ($user_mail && $user_mail->ID !== $user->ID) {
                $errors[] =  'Es existiert bereits ein anderer Nutzer mit dieser E-Mail-Adresse: <span class="review-information">' . $user_mail->user_email . '</span>';
                return $errors;
            } else {

                global $wp_roles;

                $roles_display_name = translate_user_role($wp_roles->roles[$role]['name']);


                echo '<h1> Sind Sie sicher?</h1> <span>Sie sind im Begriff, den Nutzer wie folgt zu bearbeiten:</span>';
                echo '<div class="review-information"><p>Personalnummer: ' . $login_name . '<br>';
                echo 'Name: ' . $firstname . ' ' . $lastname . '<br>';
                echo 'E-Mail: ' . $email . '<br>';
                echo 'Rolle: ' . $roles_display_name . '</p></div>';

                ?>
                <div id="final_user_editing_form">
                <form id="final_user_editing" method="post" name="final_user_editing">

                    <?php wp_nonce_field('inseider-final-edit-user','inseider-final-edit-user-nonce'); ?>

                    <input type="hidden" name="pNumber_FinalEdit" id="pNumber_FinalEdit"
                           value="<?php echo $login_name; ?>">
                    <input type="hidden" name="eMail_FinalEdit" id="eMail_FinalEdit" value="<?php echo $email; ?>">
                    <input type="hidden" name="firstname_FinalEdit" id="firstname_FinalEdit"
                           value="<?php echo $firstname; ?>">
                    <input type="hidden" name="lastname_FinalEdit" id="lastname_FinalEdit"
                           value="<?php echo $lastname; ?>">
                    <input type="hidden" name="role_FinalEdit" id="role_FinalEdit" value="<?php echo $role; ?>">

                    <button class="spk_wide_button" name="submit" value="finalEditUser" type="submit">Nutzer
                        bearbeiten
                    </button>
                </form>

                <?php

                return true;
            }
        }
    }
    else if ($_POST['submit'] == 'deleteUser') {

        if (!wp_verify_nonce($_POST['inseider-delete-user-nonce'], 'inseider-delete-user')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $login_name = sanitize_text_field($_POST['toDelete']);

        if ($login_name != null) {

            $user = get_user_by('login',$login_name);
            if ($user) {

                global $wp_roles;

                $roles_display_name = '';
                foreach ($user->roles as $role) {
                    $roles_display_name .= translate_user_role($wp_roles->roles[$role]['name']);
                }


                echo '<h1> Sind Sie sicher?</h1> <span>Sie sind im Begriff, folgenden Nutzer zu löschen:</span>';
                echo '<div class="review-information"><p>Personalnummer: ' . $user->user_login . '<br>';
                echo 'Name: ' . $user->first_name . ' ' . $user->last_name . '<br>';
                echo 'E-Mail: ' . $user->user_email . '<br>';
                echo 'Rolle: ' . $roles_display_name . '</p></div>';

                if (in_array('administrator', $user->roles)) {
                    $errors[] = 'Administratoren können nicht gelöscht werden. Aus Sicherheitsgründen, muss vor dem Löschen über die Funktion "Einzelne Benutzer bearbeiten" die Rolle verändert werden.';
                    return $errors;

                } else {


                    echo '<div id="final_user_deleting_form">';
                    echo '<form id="final_user_deleting" name="final_user_deleting" method="post">';
                    wp_nonce_field('inseider-final-delete-user','inseider-final-delete-user-nonce');
                    echo '<input type="hidden" name="toFinalDelete" id="toFinalDelete"  value="' . $user->ID . '">
                    <button class="spk_wide_button" name="submit" value="finaldeleteUser" type="submit">Nutzer endgültig löschen</button></form>';
                }

                return true;
            }
            $errors[] = 'Dieser Nutzer existiert nicht mehr';
            return $errors;
        }
    }
    else if ($_POST['submit'] == 'bulkCreateUsers') {

        if (!wp_verify_nonce($_POST['inseider-bulk-user-create-nonce'], 'inseider-bulk-user-create')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $inpur_raw = sanitize_textarea_field($_POST['bulkCreateInput']);
        global $wp_roles;

        $users_raw = explode(PHP_EOL, $inpur_raw);
        $users = array();

        foreach ($users_raw as $user_raw) {
            $tmp_user = preg_split('/\t+/', $user_raw);

            $tmp_email = $tmp_user[0];
            $tmp_login_name = $tmp_user[1];
            $tmp_tmp_firstname = $tmp_user[2];
            $tmp_lastname = $tmp_user[3];
            $tmp_role =  preg_replace('/\s+/', '', $tmp_user[4]);

            if (is_null($tmp_email) || is_null($tmp_login_name) || is_null($tmp_tmp_firstname) || is_null($tmp_lastname) || is_null($tmp_role)) {
                $errors[] = 'Mindestens ein Feld ist leer oder konnte nicht richtig eingelesen werden, überprüfen Sie die Eingabe.';
                return $errors;
            }


            $user_login = get_user_by('login',$tmp_login_name);
            $user_mail = get_user_by_email($tmp_email);

            if (!is_email($tmp_email)) {
                $errors[] = $tmp_email . ' ist keine valide E-Mail-Adresse.';
            }
            if ($user_login) {
                $errors[] = 'Der Nutzer mit dieser Personalnummer existiert bereits: ' . $user_login->user_login;
                continue;
            } else if ($user_mail) {
                $errors[] = 'Der Nutzer mit dieser E-Mail-Adresse existiert bereits: ' . $user_mail->user_email;
                continue;
            }

            switch ($tmp_role) {

                case translate_user_role($wp_roles->roles['subscriber']['name']):
                    {
                        $tmp_role_translation = 'subscriber';
                        break;
                    }
                case translate_user_role($wp_roles->roles['author']['name']):
                    {
                        $tmp_role_translation = 'author';
                        break;
                    }
                case translate_user_role($wp_roles->roles['contributor']['name']):
                    {
                        $tmp_role_translation = 'contributor';
                        break;
                    }
                case translate_user_role($wp_roles->roles['administrator']['name']):
                    {
                        $tmp_role_translation = 'administrator';
                        break;
                    }
                default:
                    {
                        $tmp_role_translation = 'ERROR';
                        $errors[] = 'Die Rolle "' . $tmp_role . '" existiert nicht';
                    }
            }

            //check for duplicates
            foreach ($users as $user){
                if($user[0]===$tmp_email){
                    $errors[] ="Die E-Mail-Adresse $tmp_email existiert mehrmals in der Eingabe.";
                }

                if($user[1]===$tmp_login_name){
                    $errors[] ="Die Personalnummer $tmp_login_name existiert mehrmals in der Eingabe.";
                }
            }
            $users[] = array($tmp_email, $tmp_login_name, $tmp_tmp_firstname, $tmp_lastname, $tmp_role, $tmp_role_translation);
        }

        if (empty($errors)) {

                    $is_final = isset($_POST['bulkCreateUsers_final']);

                    if($is_final){

                        foreach ($users as $user){

                        if (!spk_create_User($user[1], $user[0], $user[2], $user[3], $user[5])) {
                            $errors[] = 'Unbekannter Fehler beim Erstellen des Nutzers '."
                            Personalnummer: $user[0]
                            E-Mail: $user[1]<br>
                            Vorname: $user[2]<br>
                            Nachname: $user[3]<br>
                            Rolle: $user[4]<br>
                            Rolle(system): $user[5]<br>";
                            }
                        }

                                if (empty($errors)) {

                       echo '<h1>Erstellung der Nutzer erfolgreich</h1> <span>Nutzer wurden erfolgreich erstellt.</span><br><br>';
                                }else{
                                    $errors[] ='Die übrigen Nutzer wurden erfolgreich erstellt';
                                    return  $errors;
                                }
                    }else{
                        echo '<h1> Sind Sie sicher?</h1> <span>Sie sind im Begriff, folgende Nutzer zu erstellen:</span><br><br>';
                    }


            echo '
                <table style="width:100%">
                  <tr>
                    <th>E-Mail</th>
                    <th>'. $GLOBALS['Strings']['User_Management_login_label'].'</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Rolle</th>
                  </tr>';
            foreach ($users as $user) {

                echo "
                    <tr>
                        <td>$user[0]</td>
                        <td>$user[1]</td>
                        <td>$user[2]</td>
                        <td>$user[3]</td>
                        <td>$user[4]</td>
                      </tr>";
            }
            echo '</table>';

            if(!$is_final){

            ?>
            <form id="bulk_user_creating" name="bulk_user_creating" method="post" autocomplete="off">
            <?php wp_nonce_field('inseider-bulk-user-create','inseider-bulk-user-create-nonce'); ?>

                <textarea hidden id="bulkCreateInput" name="bulkCreateInput">
                <?php echo $inpur_raw; ?>

                </textarea>
                <input class="spk_wide_button" type="hidden" name ='bulkCreateUsers_final' value="true">
                <button class="spk_wide_button" name="submit" value="bulkCreateUsers" type="submit">Nutzer Erstellen</button>
            </form>

                <?php
            }
            return true;
        } else {
            return $errors;
        }
    }
    else if ($_POST['submit'] == 'finalCreateUser') {

        if (!wp_verify_nonce($_POST['inseider-final-create-user-nonce'], 'inseider-final-create-user')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $login_name = sanitize_text_field($_POST['pNumber_FinalCreate']);
        $email = sanitize_email($_POST['eMail_FinalCreate']);
        $firstname = sanitize_text_field($_POST['firstname_FinalCreate']);
        $lastname = sanitize_text_field($_POST['lastname_FinalCreate']);
        $role = sanitize_text_field($_POST['role_FinalCreate']);


        if ($login_name != null) {

            $user = get_user_by('login',$login_name);
            if ($user) {
                $errors[] = 'Der Nutzer mit dieser Personalnummer existiert bereits: <span class="review-information">' . $user->user_login . '</span><br>';
                return $errors;
            } else {

                global $wp_roles;


                $user_id = spk_create_User($login_name, $email, $firstname, $lastname, $role);
                if ($user_id) {
                    $roles_display_name = translate_user_role($wp_roles->roles[$role]['name']);

                    echo '<h1>Account wurde erstellt</h1>';
                    echo '<div class="review-information"><p>Personalnummer: ' . $login_name . '<br>';
                    echo 'Name: ' . $firstname . ' ' . $lastname . '<br>';
                    echo 'E-Mail: ' . $email . '<br>';
                    echo 'Rolle: ' . $roles_display_name . '</p></div>';
                } else {
                    echo '<h2>Fehler beim Erstellen des Nutzers</h2>';
                }

               /* if($role== 'business_partner' && isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]['size'] > 0){
                        $post = array(
                            'post_title' => $firstname.' '.$lastname,
                            'post_category' => array('70'),
                            'post_type' => 'post',
                            'post_author'=>$user_id
                        );

                        $postId = wp_insert_post($post);
                        if ($postId > 0) {
                            __update_post_meta($postId, 'media_type', 'article');
                        } else {
                            echo 'Fehler beim Uploaden des Beitrags';
                        }

                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
                        require_once(ABSPATH . "wp-admin" . '/includes/media.php');

                        $attachment_id = media_handle_upload("thumbnail", $postId);

                        if (!is_wp_error($attachment_id)) {
                            // The image was uploaded successfully!
                             set_post_thumbnail($postId, $attachment_id);
                        }
                        update_user_meta($user_id, 'business_parter_post_id', $postId);
                }*/

                 if($role== 'business_partner' && isset($_POST["boni_post_id"])){
                        $postId = $_POST["boni_post_id"];
                        update_user_meta($user_id, 'business_parter_post_id', $postId);
                }

                return true;
            }
        }
    }
    else if ($_POST['submit'] == 'finalEditUser') {

        if (!wp_verify_nonce($_POST['inseider-final-edit-user-nonce'], 'inseider-final-edit-user')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $login_name = sanitize_text_field($_POST['pNumber_FinalEdit']);
        $email = sanitize_email($_POST['eMail_FinalEdit']);
        $firstname = sanitize_text_field($_POST['firstname_FinalEdit']);
        $lastname = sanitize_text_field($_POST['lastname_FinalEdit']);
        $role = sanitize_text_field($_POST['role_FinalEdit']);


        if ($login_name != null) {

            $user = get_user_by('login',$login_name);
            if (!$user) {

                $errors[] = 'Der Nutzer mit dieser Personalnummer existiert nicht: <span class="review-information">' . $login_name . '</span>';
                return $errors;
            } else {

                global $wp_roles;

                if (spk_edit_User($login_name, $email, $firstname, $lastname, $role)) {
                    $roles_display_name = translate_user_role($wp_roles->roles[$role]['name']);
                    echo '<h1>Account wurde bearbeitet</h1>';
                    echo '<div class="review-information"><p>Personalnummer: ' . $login_name . '<br>';
                    echo 'Name: ' . $firstname . ' ' . $lastname . '<br>';
                    echo 'E-Mail: ' . $email . '<br>';
                    echo 'Rolle: ' . $roles_display_name . '</p></div>';
                } else {
                    echo '<h2>Fehler beim Bearbeiten des Nutzers</h2>';
                }


                return true;
            }
        }
    }
    else if ($_POST['submit'] == 'finaldeleteUser') {

        if (!wp_verify_nonce($_POST['inseider-final-delete-user-nonce'], 'inseider-final-delete-user')) {
            $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
            return $errors;
        }

        $id = sanitize_text_field($_POST['toFinalDelete']);
        if (in_array('administrator', get_user_by('id', $id)->roles)) {
            $errors[] = 'Administratoren können nicht gelöscht werden';
            return $errors;
        } else {
            if (wp_delete_user($id, 1)) {
                echo '<h1>Nutzer erfolgreich gelöscht</h1>';
                return true;
            } else {
                $errors[] = 'Nutzer konnte nicht gelöscht werden';
                return $errors;
            }
        }
    }
     else if ($_POST['submit'] == 'list_all_users') {

         $users = get_users();
         $data = null;

                     echo '<table><tr>
                    <th>'. $GLOBALS['Strings']['User_Management_login_label'].'</th>
    <th>E-Mail-Adresse</th>
    <th>Vorname</th>
    <th>Nachname</th>
    <th>Rolle</th>
  </tr>';
$count=0;
global $wp_roles;
         foreach ($users as $user) {
             $count++;
            $data = get_userdata($user->ID);
            $role = array_pop($data->roles);
            $role = $wp_roles->roles[$role]['name'];

            echo "<tr><td> ".$user->data->user_login."</td><td> ".$user->data->user_email."</td><td> ".$data->first_name."</td><td> ".$data->last_name."</td><td> ".$role."</td></tr>";
         }
                     echo '</table>';

         echo "<p>Insgesamt gibt es $count Nutzer.</p><br>";

    }
    else {
        return false;
    }

    return true;
}


genesis();

