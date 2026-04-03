<?php

// Add our custom loop
add_action('genesis_loop', 'inseider_loop');
function inseider_loop()
{

    global $restrictedMode;

    form_js();
    padStart_js();
    render_loader();
    handle_form_submit();

    $edit_Post = null;
    $edit_Post_meta = null;
    if (isset($_GET['edit_post'])) {
        $edit_Post = get_post($_GET['edit_post']);
        $edit_Post_meta = get_post_meta($_GET['edit_post']);
    }


    echo "
<style>

</style>
    <script type=\"text/javascript\" src=\"/wp-content/themes/inseider/js/heic2any/dist/heic2any.js\"></script>

        <script src=\" " . get_stylesheet_directory_uri() . "/js/tinymce/tinymce.min.js\" referrerpolicy=\"origin\"></script>
        
        <script>   (function () {
            window.onpageshow = function(event) {
                    if(window.location.href === '" . home_url() . "' ||
                    window.location.href === '" . home_url() . "/' ||
                    window.location.href+'/' === '" . home_url() . "' ){
                        window.location.reload();
                        }
                        
                        if(document.location.hash === '#home'){
                        document.location.href='" . home_url() . "';
                        }
            };
        })();</script>";

    if ($GLOBALS['render_form']) {

        ?>
        <div id="postbox" style="display: none" class="postbox">
            <form id="new_post" name="new_post" method="post" onsubmit="return showLoading(true)"
                  enctype="multipart/form-data">

                <?php wp_nonce_field('wps-frontend-post', 'wps-frontend-post-nonce'); ?>
                <div <?php if ($edit_Post) {
                    echo 'style="display:none"';
                } ?>>
                    <span>Was möchten Sie posten?</span>
                    <div>
                        <label class="select-label" for="mediaSelect">
                            <?php
                            global $current_user;
                            echo get_mediaSelect_html($current_user);
                            ?>
                        </label>
                        <br/>
                    </div>
                </div>

                <div id="categoryCont">

                    <div>
                        <?php
                        global $current_user;

                        $categories = array();
                        foreach ($current_user->roles as $role) {

                            if (current_user_can('Developer')) {
                                $role = 'Developer';
                            }

                            foreach (get_categories_by_role()[$role] as $cat) {

                                $already_saved = false;

                                foreach ($categories as $saved_cat) {
                                    if ($saved_cat->term_id == $cat) {
                                        $already_saved = true;
                                    }
                                }

                                if (!$already_saved) {
                                    $categories[] = get_category($cat);
                                }
                            }
                        }

                        if (sizeof($categories) == 0) {
                            wp_safe_redirect(home_url());
                        }

                        $disabled = sizeof($categories) < 2 ? ' disabled' : '';

                        echo '<span class="' . $disabled . '" for="post_category">Wo möchten Sie posten?</span>';
                        echo '<label class="select-label" ' . $disabled . ' for="post_category">';
                        echo '<select name="cat" id="cat" class="postform ' . $disabled . '">';
                        foreach ($categories as $cat) {

                            if ($cat->term_id == 1) {
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            }

                            if (in_array($cat->term_id, hidden_categories())) {
                                $hidden = ' hidden ';
                            } else {
                                $hidden = '';
                            }

                            $category_has_drafts = 'false';
                            if (category_has_drafts($cat->term_id)) {
                                $category_has_drafts = 'true';
                            }

                            $admin_activation_needed = 'false';
                            if (admin_activation_needed(null, array($cat->term_id))) {
                                $admin_activation_needed = 'true';
                            };

                            $data_comments = force_post_comments(null, array($cat->term_id));

                            $data_notify = 'false';

                            global $NOTIFICATION_SETTINGS;
                            $pns = get_notification_settings($cat->term_id);
                            if ($pns == $NOTIFICATION_SETTINGS['NEVER']) {
                                $data_notify = 'NEVER';
                            } elseif ($pns == $NOTIFICATION_SETTINGS['ALWAYS']) {
                                $data_notify = 'ALWAYS';
                            } else {
                                $data_notify = 'CHOOSE';
                            }


                            $data_cat_forces_media = category_forces_media($cat->term_id);

                            echo '<option data-cat_forces_media="' . $data_cat_forces_media . '" data-drafts="' . $category_has_drafts . '" data-admin_activation="' . $admin_activation_needed . '"  data-notifications="' . $data_notify . '" data-comments="' . $data_comments . '" class="level-0" ' . $selected . $hidden . ' value="' . $cat->term_id . '">' . $cat->name . '</option>';
                        }
                        echo '</select>';
                        ?>
                        </label>
                    </div>
                </div>

                <div id="videoCont">
                    <label for="videoFile">Video:</label>
                    <div id="additionalVideoInformation"></div>
                    <input type="file" class="accepts_drops" name="videoFile" id="videoFile" accept="video/*">
                </div>

                <div id="titleCont">
                    <label for="title">Titel
                        <input type="text" id="title" value="" required tabindex="0" size="20" name="title"/>
                    </label>
                </div>

                <div id="subheadingCont">
                    <label id="subheadingLabel" for="subheading">Beschreibungstext</label>
                    <textarea oninput="auto_grow(this)" <?php echo get_input_max_length('subheading') ?> id="subheading"
                              tabindex="0" name="subheading" cols="20"
                              rows="3"><?php
                        if ($edit_Post) {
                            echo esc_textarea($edit_Post->post_excerpt);
                        }
                        ?></textarea>
                    <br/>
                </div>


                <div id="thumbnailCont">
                    <span class="conversion_notice">Konvertierung läuft,<br> bitte warten Sie.</span>
                    <label id="thumbnail_label" for="thumbnail">Vorschaubild:</label>
                    <div class="thumbnailfileCont">
                        <input type="file" class="accepts_drops" name="thumbnail" id="thumbnail"
                               accept="image/png, image/jpeg">
                    </div>
                    <input type="hidden" name="template_thumbnail" id="template_thumbnail">
                </div>

                <input type="hidden" id="content_attachment_ids" name="content_attachment_ids">

                <div id="contentCont">
                    <label for="content">Inhalt</label>
                    <textarea oninput="auto_grow(this)" id="content" tabindex="0" name="content" cols="50"
                              rows="16"><?php
                        if ($edit_Post) {
                            echo esc_textarea(wpautop($edit_Post->post_content, true));
                        }
                        ?></textarea>
                </div>

                <div id="galleryCont">
                    <span class="conversion_notice">Konvertierung läuft,<br> bitte warten Sie.</span>
                    <label for="gallery">Galerie:</label>
                    <div class="galleryfileCont multiple_images">
                        <input type="file" class="accepts_drops" name="gallery[]" id="gallery" multiple="multiple"
                               accept="image/png, image/jpeg">
                    </div>
                </div>

                <div id="AudioCont">
                    <label for="audio">Audiodatei:</label>
                    <input type="file" class="accepts_drops" name="audio" id="audio" accept="audio/mpeg3">
                </div>

                <div id="additional_prize_draw_inputs">

                    <div <?php if ($edit_Post) {
                        echo 'style="display:none"';
                    }
                    ?>>
                        <input type="checkbox" checked id="is_event" name="is_event">
                        <label for="is_event">Es werden Freikarten für ein Event verlost.</label>
                    </div>


                    <input type="checkbox" id="tax_free" name="tax_free" checked>
                    <label for="tax_free">Die Karten sind für den Gewinner steuer- und
                        sozialversicherungsfrei.</label>

                    <br>
                    <?php echo get_prize_draw_template_datalists(); ?>

                    <div id="prize_draw_from">
                        <br>
                        <p class="group-lable">Start des Gewinnspiels</p>
                        <input type="checkbox" id="prize_draw_starting_now"
                               name="prize_draw_starting_now[]">
                        <label for="prize_draw_starting_now">Direkt nach Freigabe</label> <br>
                        <div class="datepicker">
                            <label id="from_datepicker_lable" for="from_datepicker">Datum</label>
                            <input type='text' autocomplete="off" tabindex="0"
                                   name="from_datepicker" class="date_time_picker"
                                   onchange="validate_date(this)" id='from_datepicker'/>
                        </div>
                        <div class="timepicker">
                            <label id="from_timepicker_lable" for="from_timepicker">Uhrzeit</label>
                            <input type='text' autocomplete="off"
                                   name="from_timepicker" value="12:00" class="date_time_picker"
                                   onchange="validate_time(this)" id='from_timepicker'/>
                        </div>
                    </div>

                    <div id="prize_draw_event_form_containter">
                        <div class="prize_draw_event_form" id="prize_draw_event_form_0">

                            <div id="delete_prize_draw_event_form_container_0">
                                <?php
                                $visible = ($edit_Post_meta['prize_draw_visible'][0] != 'false');

                                if (!$edit_Post || !$visible) {
                                    ?>
                                    <a class="delete_prize_draw_event_form" id="delete_prize_draw_event_form_0"
                                       onclick="remove_prize_draw_event_form(this.id.split('_').pop())">Termin
                                        löschen</a>
                                    <br>
                                    <?php
                                }
                                ?>
                            </div>

                            <?php echo get_prize_draw_templates(); ?>

                            <p style="margin-bottom: 0.2em" id="event_info_0">Im Rahmen unseres Sponsorings verlosen
                                wir:</p>
                            <div class="form_inline">

                                <select class="form_small" name="number_of_tickets[]" id="number_of_tickets_0">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                                <span>&nbsp;x </span>
                                <select class="form_small" name="amount_of_tickets[]" id="amount_of_tickets_0">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                                <span id="event_info_two_0">Freikarten für:</span><br>
                            </div>
                            <div style="margin-top: 0.5em;">
                                <input class="desktop-half-inline" tabindex="0" placeholder="das Spiel"
                                       autocomplete="no"
                                       type="text"
                                       name="event_type[]"
                                       id="event_type_0">
                                <input class="desktop-half-inline float-right" tabindex="0" placeholder="XY gegen Z"
                                       autocomplete="no"
                                       type="text" name="event_name[]"
                                       id="event_name_0">
                            </div>


                            <div id="event_start_0">
                                <div class="datepicker desktop-half-inline">
                                    <label for="event_start_datepicker_0">am</label>
                                    <input type='text' autocomplete="off" tabindex="0" name="event_start_datepicker[]"
                                           id='event_start_datepicker_0' class="date_time_picker"/>
                                </div>
                                <div class="timepicker desktop-half-inline float-righ">
                                    <label for="event_start_timepicker_0">um</label>
                                    <input type='text' autocomplete="off" tabindex="0" class="date_time_picker"
                                           name="event_start_timepicker[]"
                                           id='event_start_timepicker_0'
                                           value="12:00"/>
                                </div>
                            </div>

                            <div id="event_location_cont_0">
                                <label for="event_location_0">Location</label>
                                <input type="text" placeholder="Saturn Arena" tabindex="0" autocomplete="no"
                                       name="event_location[]"
                                       id="event_location_0">
                            </div>


                            <div id="prize_draw_to_0">
                                <p class="group-lable">Ende des Gewinnspiels</p>
                                <div class="datepicker">
                                    <label for="to_datepicker_0">Datum</label>
                                    <input type='text' autocomplete="off" class="date_time_picker"
                                           onchange="validate_date(this)" name="to_datepicker[]"
                                           id='to_datepicker_0'/>
                                </div>
                                <div class="timepicker">
                                    <label for="to_timepicker_0">Uhrzeit</label>
                                    <input type='text' autocomplete="off" class="date_time_picker"
                                           onchange="validate_time(this)" name="to_timepicker[]"
                                           id='to_timepicker_0'
                                           value="12:00"/>
                                </div>
                            </div>

                            <div id="prize_draw_email_Cont_0">
                                <label for="prize_draw_email_0">E-Mail-Adresse für Resultat
                                    <input type="email" id="prize_draw_email_0"
                                           value="<?php echo wp_get_current_user()->user_email ?>"
                                           size="20"
                                           name="prize_draw_email[]"/>
                                </label>

                            </div>
                        </div>
                    </div>
                    <a class="button spk_wide_button" id="add_prize_draw_event_form"
                       onclick="add_prize_draw_event_form()">Termin hinzufügen</a>
                </div>

                <div id="DocumentCont">

                    <label for="document">PDF-Anhang:</label>
                    <div id="document_preview_images" onclick="clickThrough('document')"></div>
                    <input type="file" class="accepts_drops" multiple="multiple" name="document[]" id="document"
                           accept="application/pdf">
                </div>

                <?php
                custom_uploading_html();
                ?>

                <div id="tagsCont">
                    <label for="post_tags">Tags (getrennt durch Komma)
                        <input type="text" value="" tabindex="0" size="16" name="post_tags" id="post_tags"/>
                    </label>
                    <br>
                </div>


                <div id="allow_comments_Cont" style="display: none">
                    <input type="checkbox" id="allow_comments" name="allow_comments"

                        <?php if ($edit_Post && 'false' == $edit_Post_meta['allow_comments'][0]) {
                        } else {
                            //todo test
                            echo 'checked';
                        } ?>>
                    <label for="allow_comments">Kommentare erlauben</label>
                </div>

                <?php
                if (!$restrictedMode) {
                    ?>

                    <div id="author_Cont" style="display: none">
                        <input type="checkbox" checked id="author_me" name="author_me">
                        <label for="author_me"><?php if ($edit_Post) {
                                echo 'Autor beibehalten';
                            } else {
                                echo 'Ich bin der Autor';
                            } ?></label>
                        <br>
                        <div id="author_id_cont" style="display: none">
                            <label for="author_id">Name / Personalnummer des Autors</label>
                            <input type='text' list="userList" autocomplete="off" name="author_id" id='author_id'/>
                            <datalist id="userList">
                                <?php
                                get_users_datalist_options();
                                ?>
                            </datalist>
                        </div>
                    </div>

                    <div id="post_invisible_cont" style="display: none">
                    <input type="checkbox" id="post_invisible" name="post_invisible"

                        <?php if ($edit_Post && 'true' == $edit_Post_meta['post_invisible'][0]) {
                            echo 'checked';
                        } ?>>
                    <label for="post_invisible">Beitrag verstecken</label>
                    <p style="color:gray;">Versteckte Beiträge erscheinen für Benutzer nicht in den Feeds, können aber von jedem über den Link erreicht werden.</p>
                </div>

                    <?php
                }
                if (!$restrictedMode && (!$edit_Post || get_post_status($edit_Post) != 'publish')) {
                    ?>


                    <div id="post_later_Cont" style="display: none">
                        <input type="checkbox" id="post_later" name="post_later">
                        <label for="post_later">Terminiert veröffentlichen</label>
                        <br>
                        <div class="datepicker" id="post_later_date_cont" style="display: none">
                            <label for="post_later_date">Datum</label>
                            <input type='text' autocomplete="off" name="post_later_date" id='post_later_date'/>
                        </div>
                        <div class="timepicker" id="post_later_time_cont" style="display: none">
                            <label for="post_later_time">Uhrzeit</label>
                            <input type='text' autocomplete="off" name="post_later_time" id='post_later_time'
                                   value="12:00"/>
                            <br>
                            <br>
                        </div>
                    </div>
                    <?php
                }
                if ($edit_Post) {

                    $tags_string = ' ';
                    foreach (get_the_tags($edit_Post->ID) as $tag) {
                        $tags_string .= $tag->name . ', ';
                    }
                    //remove last ', '
                    $tags_string = substr($tags_string, 0, -2);

                    //set input so the post will be edited and not created new and set other form fields


                    echo '<input type="hidden" name="edit_post" id="edit_post" value="' . $edit_Post->ID . '"/>
                    <script>' . "
               
               
                    </script>";
                }
                ?>


                <div id="video-demo-container">
                    <video style="display: none" id="main-video" controls>
                        <source id="main-video-source" type="video/mp4">
                    </video>
                    <canvas id="video-canvas" style="display: none"></canvas>
                </div>

                <br>
                <p style="display: none" id="uploading_info"></p>

                <div class="flex_button_container">
                    <input id="sumbitButton" type="submit" class="spk_wide_button flex_button" name="submit"
                           value="hochladen"/>
                    <a id="createPostCancelButton" href="<?php echo home_url(); ?>"
                       class="button flex_button spk_wide_button secondary_button" style="margin-left: 0">
                        abbrechen
                    </a>
                </div>
            </form>

            <audio id="hidden_Audio_output"></audio>


            <?php


            //import splitted files
            $roots_includes = array(
                '/uploading/js.php'
            );

            foreach ($roots_includes as $file) {
                if (!$filepath = locate_template($file)) {
                    trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
                }

                require_once $filepath;
            }
            unset($file, $filepath);


            ?>


        </div>

        <?php
    }

}