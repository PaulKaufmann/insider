<?php
/**
 * Version 2.0.1
 * Template Name: Kommunikation
 */

add_action('genesis_meta', 'handle_viewing_restrictions', 500);
remove_action('genesis_entry_header', 'genesis_do_post_title');

add_action('genesis_loop', 'communications_loop');
function communications_loop()
{
    if (!can_sick_notes_be_submitted() && !can_ideas_be_submitted()) {
        wp_safe_redirect(home_url());
        exit;
    }

    $sick_note_disabled = '';
    $ideas_disabled = '';
    if (!can_sick_notes_be_submitted()) {
        $sick_note_disabled = 'disabled';
    }
    if (!can_ideas_be_submitted()) {
        $ideas_disabled = 'disabled';
    }

    render_loader();
    form_js();
    ?>

    <div class="communication-content">

        <?php

        echo '<style>' . custom_css('communication') . '</style>';

        $form_result = handle_communication_form_submit();
        if (is_wp_error($form_result)) {
            $error = $form_result->get_error_message();
            echo "<div class=\"error_message\">
             <h3>Fehler:</h3>
             <p><strong>$error</strong></p>
             </div>";
        }

        if ($form_result !== true) {

            ?>
            <div id="communication-initial-container">
                <div class='post-top-bar'>
                    <a class="noHighlight" href="/"><p id='inseider-back-Button'>
                            <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück</p>
                    </a>
                </div>

                <img src="<?php echo get_Client_Image_Dir() . 'interaction_icon.svg'; ?>">
                <h1>Einfach machen</h1>
                <div>
                    <button <?php echo $sick_note_disabled; ?> onclick="showCommunicationContainer(1)"
                                                               class="spk_wide_button <?php echo $sick_note_disabled; ?>">
                        Krankmeldung
                    </button>
                </div>
                <div>
                    <button <?php echo $ideas_disabled; ?> onclick="showCommunicationContainer(2)"
                                                           class="spk_wide_button <?php echo $ideas_disabled; ?>">
                        <?php echo $GLOBALS['Strings']['idea_label']; ?>
                    </button>
                </div>
            </div>

            <div id="communication-sub-container" style="display:none;">
                <div class='post-top-bar' onclick="showCommunicationContainer(-1)">
                    <p id='communication-back-Button'>
                        <?php echo file_get_contents(get_Server_Image_Dir() . "small_back_icon.svg"); ?> zurück
                    </p>
                </div>
            </div>

            <div id="communicationContainer1" class="postbox" style="display: none">
                <h2>Krankmeldung</h2>
                <br>
                <p>
                    <?php echo $GLOBALS['Strings']['sick_note_terms']; ?>
                </p>
                <input type="checkbox" id="read_terms_sn">
                <label for="read_terms_sn">Bedingungen gelesen</label>
                <br>
                <br>
                <form id="sick_Note_Form" name="sick_Note_Form" method="post"
                      onsubmit="return showLoading(true)" autocomplete="off" enctype="multipart/form-data">

                    <div id="sn_terms_accepted_content" style="display: none">
                        <?php wp_nonce_field('inseider-sick-note'); ?>

                        <div id="sick_note_drop_zone">
                            <label for="sickNote">Foto der AU:</label>
                            <input type="file" name="sickNote" id="sickNote"
                                   accept="image/png, image/jpeg">
                        </div>
                        <label for="sn_remark"><?php echo $GLOBALS['Strings']['client_sick_note_subject'] ?></label>
                        <textarea required oninput="auto_grow(this)" name="sn_remark" id="sn_remark"></textarea>
                        <br>
                        <br>
                    </div>
                    <div class="flex_button_container">
                        <button disabled class="spk_wide_button flex_button" style="display: none" id="submit_sn"
                                name="submit" value="sickNote" type="submit">abschicken
                        </button>
                        <button onclick="showCommunicationContainer(-1)"
                                class="spk_wide_button secondary_button flex_button">abbrechen
                        </button>
                    </div>
                </form>
            </div>

            <div id="communicationContainer2" class="postbox" style="display: none">
                <h2><?php echo $GLOBALS['Strings']['idea_label']; ?></h2>
                <br>
                <p>
                    <?php echo $GLOBALS['Strings']['idea_terms']; ?>
                </p>
                <input type="checkbox" id="read_terms_idea">
                <label for="read_terms_idea">Bedingungen gelesen</label>
                <br>
                <br>
                <form id="idea_Form" name="idea_Form" method="post"
                      onsubmit="return showLoading(true)" autocomplete="off"
                      enctype="multipart/form-data">

                    <div id="idea_terms_accepted_content" style="display: none">

                        <?php wp_nonce_field('inseider-idea'); ?>
                        <div id="communication_receiver_cont">
                            <label for="idea_receiver">Verantwortlicher:</label>
                            <select name="idea_receiver">
                                <?php idea_receiver_select_html() ?>
                            </select>
                            <br>
                            <br>
                        </div>
                        <div id="idea_drop_zone">
                            <label for="idea">Optionale Fotos zur Verdeutlichung</label>
                            <input type="file" name="idea[]" id="idea" multiple="multiple"
                                   accept="image/png, image/jpeg">
                        </div>
                        <label for="idea_remark">Beschreibung:</label>
                        <textarea oninput="auto_grow(this)" name="idea_remark" required id="idea_remark"></textarea>
                        <br>
                        <br>
                    </div>
                    <div class="flex_button_container">
                        <button class="spk_wide_button flex_button" style="display: none" disabled id="submit_idea"
                                name="submit" value="idea"
                                type="submit">abschicken
                        </button>
                        <button onclick="showCommunicationContainer(-1)"
                                class="spk_wide_button secondary_button flex_button">abbrechen
                        </button>
                    </div>

                </form>
            </div>


            <?php
        } else {
            ?>
            <div id="communication-initial-container">
                <img src="<?php echo get_Client_Image_Dir() . 'interaction_icon.svg'; ?>">
                <h1>Einfach machen</h1>
                <h2><?php echo $GLOBALS['Strings']['communications_thank_you']; ?></h2>
                <br>
                <a href="<?php echo home_url(); ?>">
                    <button class="spk_wide_button">Startseite</button>
                </a>
            </div>
            <?php
        }
        ?>
    </div>

    <script>
        function showCommunicationContainer(index) {
            document.getElementById('communicationContainer1').style.display = 'none';
            document.getElementById('communicationContainer2').style.display = 'none';
            if (index > 0) {
                document.getElementById('communication-sub-container').style.display = 'block';
                document.getElementById('communication-initial-container').style.display = 'none';
                document.getElementById('communicationContainer' + index).style.display = 'block';
            } else {
                document.getElementById('communication-sub-container').style.display = 'none';
                document.getElementById('communication-initial-container').style.display = 'block';
            }
        }


        window.addEventListener('load', function (event) {

            <?php echo custom_js('communication');?>

            let file_uploader_captions = <?php echo file_uploader_captions();?>;

            //maxFileSize is defined in config file
            file_uploader_captions['feedback'] = "<?php echo sprintf($GLOBALS['Strings']['emptyTextSickNote'], $GLOBALS['maxFileSize']); ?>";
            jQuery('#sickNote').fileuploader({

                captions: file_uploader_captions,
                enableApi: true,
                limit: 1,
                maxSize: <?php echo $GLOBALS['maxFileSize']; ?>,
                theme: 'default',
                dragDrop: {
                    container: '#sick_note_drop_zone',
                },
                addMore: false
            });

            file_uploader_captions['feedback'] = "<?php echo sprintf($GLOBALS['Strings']['emptyTextIdea'], $GLOBALS['maxFileSize']);?>";
            jQuery('#idea').fileuploader({
                captions: file_uploader_captions,
                enableApi: true,
                limit: 3,
                maxSize: <?php echo $GLOBALS['maxFileSize']; ?>,
                theme: 'default',
                dragDrop: {
                    container: '#idea_drop_zone',
                },
                addMore: true
            });


            showLoading(false);

            var snTerms = document.getElementById("read_terms_sn");
            if (snTerms) {
                snTerms.onchange = function () {
                    document.getElementById('submit_sn').disabled = !this.checked;
                    var form_display = 'none';
                    var submit_display = 'none';
                    if (this.checked) {
                        form_display = 'block';
                        submit_display = 'inline-block';
                    }
                    document.getElementById('sn_terms_accepted_content').style.display = form_display;
                    document.getElementById('submit_sn').style.display = submit_display;
                };
            }

            var ideaTerms = document.getElementById("read_terms_idea");
            if (ideaTerms) {
                ideaTerms.onchange = function () {
                    document.getElementById('submit_idea').disabled = !this.checked;
                    var form_display = 'none';
                    var submit_display = 'none';
                    if (this.checked) {
                        form_display = 'block';
                        submit_display = 'inline-block';
                    }
                    document.getElementById('idea_terms_accepted_content').style.display = form_display;
                    document.getElementById('submit_idea').style.display = submit_display;
                };
            }


        });

    </script>

    <?php
}

/**
 * Validates form data and sends the required E-Mails if successful
 * @return WP_Error|bool If there are errors return a WP_Error object, otherwise a bool indicating if the form should be printed again
 */
function handle_communication_form_submit(): WP_Error|bool
{
    if (!empty($_POST['submit']) && $_POST['submit'] == 'sickNote') {

        if (!wp_verify_nonce($_POST['_wpnonce'], 'inseider-sick-note')) {
            return new WP_Error('form_error', 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut.');
        }

        $user = wp_get_current_user();
        $fullname = $user->first_name . ' ' . $user->last_name;
        if (isset($_POST['sn_remark']) && $_POST['sn_remark'] != '') {
            $remark = sanitize_textarea_field($_POST['sn_remark']);
        } else {
            $remark = '';
        }

        // renaming the file to get rid of possibly sensible data like timestamps, renaming will not break pngs
        $newname = 'AU_' . time() . '.jpg';
        rename($_FILES['sickNote']['tmp_name'], $newname);
        $mail_attachment = array($newname);

        $user_message = sprintf($GLOBALS['Strings']['client_sick_note_message'], $fullname, $remark);

        $result_user = spk_sendEmail($user->user_email,
            $user_message,
            $GLOBALS['Strings']['client_sick_note_subject'],
            false, array(), false, false);

        //users additional secondary email address
        $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);

        if ($custom_mail != '') {
            spk_sendEmail($custom_mail,
                $user_message,
                $GLOBALS['Strings']['client_sick_note_subject'],
                false, array(), false, false);
        }

        $admin_message = sprintf($GLOBALS['Strings']['admin_sick_note_message'], $fullname, get_domain(), $remark);

        $result_admin = spk_sendEmail(get_sick_note_Email(),
            $admin_message,
            "Krankmeldung $fullname",
            false,
            $mail_attachment, true, false);

        // delete image file
        unlink($newname);

        if (!$result_user || !$result_admin) {
            return new WP_Error('error_sending_mail', 'Beim Senden der Nachricht sind Fehler aufgetreten. Eventuell sind die Anhänge zu groß.');
        }
        return true;

    } else if (!empty($_POST['submit']) && $_POST['submit'] == 'idea') {

        if (isset($_POST['idea_remark']) && $_POST['idea_remark'] != '') {
            $remark = sanitize_textarea_field($_POST['idea_remark']);
        } else {
            return new WP_Error('no_remark', 'Bitte fügen Sie eine Beschreibung hinzu.');
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'inseider-idea')) {
            return new WP_Error('form_error', 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut.');
        }

        $image_Files = array();

        if (sizeof($_FILES['idea']['tmp_name']) > 4) {
            return new WP_Error('to_many_images', 'Bitte laden Sie höchstens 3 Bilder hoch.');
        }

        $totalSize = 0;
        foreach ($_FILES['idea']['size'] as $file) {
            $totalSize += $file / 1024 / 1024;
        }

        //maxFileSize is defined in config file
        if ($totalSize > $GLOBALS['maxFileSize']) {
            return new WP_Error('max_fiesize_exceeded', 'Die Hochgeladenen Bilder sind zu groß.');
        }

        $count = 1;
        foreach ($_FILES['idea']['tmp_name'] as $file) {
            $tmp_newname = 'Hinweis_' . $count++ . time() . '.jpg';
            rename($file, $tmp_newname);
            $image_Files[] = $tmp_newname;
        }

        $receiver = '';
        $ccreceiver = false;

        if (isset($_POST['idea_receiver']) && $_POST['idea_receiver'] != '') {
            $ir = get_idea_receiver($_POST['idea_receiver']);
            $receiver = $ir['receiver'];
            $ccreceiver = $ir['cc_receiver'];
        } else {
            return new WP_Error('no_receiver', 'Kein Verantwortlicher ausgewählt.');
        }

        $user = wp_get_current_user();
        $fullname = $user->first_name . ' ' . $user->last_name;

        //for easier later reference each idea gets an ID
        $idea_id = get_option('idea_count') + 1;
        update_option('idea_count', $idea_id);

        $user_message = sprintf($GLOBALS['Strings']['client_idea_message'], $fullname, $remark, $idea_id);

        spk_sendEmail($user->user_email,
            $user_message,
            $GLOBALS['Strings']['client_idea_subject'],
            false,
            $image_Files, false, false);

        //users additional secondary email address
        $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);

        if ($custom_mail != '') {
            spk_sendEmail($custom_mail,
                $user_message,
                $GLOBALS['Strings']['client_idea_subject'],
                false,
                $image_Files, false, false);
        }

        $admin_message = sprintf($GLOBALS['Strings']['admin_idea_message'], $fullname, $remark, $idea_id);
        $admin_subject = sprintf($GLOBALS['Strings']['admin_idea_subject'], $fullname, $idea_id);

        spk_sendEmail($receiver,
            $admin_message,
            $admin_subject,
            $ccreceiver,
            $image_Files, true, false);

        // delete image files
        foreach ($image_Files as $file_name) {
            unlink($file_name);
        }
        return true;
    }

    return false;
}

genesis();

?>