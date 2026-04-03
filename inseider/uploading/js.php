<?php
if (!isset($edit_Post)) {
    $edit_Post = null;
}

if (!isset($edit_Post_meta)) {
    $edit_Post_meta = null;
}

if (!isset($tags_string)) {
    $tags_string = ' ';
}

?>


<script>

    window.addEventListener('load', function (event) {

        <?php
        /*        jQuery('#event_type_0').hide();

                jQuery('#subheadingCont label').addClass( "disabled" );

                jQuery('#subheading').prop( "disabled", true );*/
        $caf = customize_uploading_fields(reset(wp_get_current_user()->roles));

        foreach ($caf as $action => $handles) {
            $action_js = '';
            switch ($action) {
                case 'hidden':
                {
                    $action_js = '.addClass( "hidden" )';
                    break;
                }
                case 'disabled':
                {
                    $action_js = '.prop( "disabled", true )';
                    break;
                }
                case 'disabled_class':
                {
                    $action_js = '.addClass( "disabled_permanently" )';
                    break;
                }
                case 'change_html':
                {
                    $action_js = '.html ("%s")';
                }
            }
            $tmp_action_js = $action_js;
            foreach ($handles as $param => $handle) {
                if (!is_numeric($param)) {
                    //explicit param as key
                    $tmp_action_js = sprintf($action_js, $param);
                } else {
                    $tmp_action_js = $action_js;
                }
                /*
                $position_seperator = strpos($handle,'|');
                if($position_seperator){

                    $handle = substr($handle,0,$position_seperator);
                    param = substr($handle,0,$position_seperator);
                }*/
                echo "jQuery('$handle')" . $tmp_action_js . ';';
            }
        }
        ?>

        jQuery('#is_event').change(function () {
            if (this.checked) {
                jQuery('#event_name_0').css('float', 'right').attr("placeholder", "Romeo und Julia");
                jQuery('#event_type_0').show();
                jQuery('#event_start_0').show();
                jQuery('#event_location_cont_0').show();
                jQuery('#add_prize_draw_event_form').show();
                jQuery('#prize_draw_template_cont_0').show();
                jQuery('#event_info_0').show();
                jQuery('#event_info_two_0').show();
            } else {
                jQuery('.prize_draw_event_form:not(#prize_draw_event_form_0)').remove();
                jQuery('#event_name_0').attr("placeholder", "Kinogutscheine").css('float', 'unset');
                jQuery('#event_type_0').hide();
                jQuery('#event_start_0').hide();
                jQuery('#event_location_cont_0').hide();
                jQuery('#add_prize_draw_event_form').hide();
                jQuery('#prize_draw_template_cont_0').hide();
                jQuery('#event_info_0').hide();
                jQuery('#event_info_two_0').hide();
            }
        });

        <?php

        //region set edit_post Values
        if ($edit_Post) {

            $allow_Comments = ($edit_Post_meta['allow_comments'][0] != 'false');

            custom_media_edit_post_values($edit_Post, $edit_Post_meta);

            /* excerpt and content is injected into the html, to handle linebreaks*/
            echo "  jQuery('h1.entry-title').text('Post bearbeiten: " . addslashes($edit_Post->post_title) . "');
                                    jQuery('#mediaSelect').val('" . $edit_Post_meta['media_type'][0] . "');
                                    jQuery('#title').val('" . addslashes($edit_Post->post_title) . "');
                                    jQuery('#post_tags').val('" . addslashes($tags_string) . "');
                                    jQuery('#cat').val('" . $edit_Post->post_category[0] . "');
                                    jQuery('#allow_comments').prop(\"checked\"," . $allow_Comments . ");

                                    auto_grow(document.getElementById(\"content\"));
                                    ";

            if ($edit_Post_meta['media_type'][0] == 'prize_draw') {

                $prize_draw_from = ($edit_Post_meta['prize_draw_from'][0]);

                echo "jQuery('#tax_free').prop(\"checked\"," . isset($edit_Post_meta['prize_draw_tax_free'][0]) . ");
                                  jQuery('#is_event').prop(\"checked\"," . $edit_Post_meta['prize_draw_is_event'][0] . ").change();
                                                                          jQuery('#from_datepicker').val('" . substr($prize_draw_from, 0, -5) . "');
                                        jQuery('#from_timepicker').val('" . substr($prize_draw_from, -5, 5) . "');
                                  ";


                foreach (unserialize($edit_Post_meta['prize_draw_number_of_tickets'][0]) as $i => $number_of_Tickets) {

                    if ($i > 0) {
                        echo "add_prize_draw_event_form();";
                    }
                    if (!$number_of_Tickets) {
                        $number_of_Tickets = 1;
                    }

                    $amount_of_tickets = addslashes(unserialize($edit_Post_meta['prize_draw_amount_of_tickets'][0])[$i]);
                    $event_type = addslashes(unserialize($edit_Post_meta['prize_draw_event_type'][0])[$i]);
                    $event_name = addslashes(unserialize($edit_Post_meta['prize_draw_event_name'][0])[$i]);
                    $location = addslashes(nl2br(unserialize($edit_Post_meta['prize_draw_event_location'][0])[$i]));
                    $start_date = addslashes(unserialize($edit_Post_meta['prize_draw_event_start_date'][0])[$i]);
                    $start_time = addslashes(unserialize($edit_Post_meta['prize_draw_event_start_time'][0])[$i]);
                    $prize_draw_to = addslashes(unserialize($edit_Post_meta['prize_draw_to'][0])[$i]);
                    $email = addslashes(unserialize($edit_Post_meta['prize_draw_email'][0])[$i]);

                    echo "jQuery('#number_of_tickets_" . $i . "').val('" . $number_of_Tickets . "');
                                        jQuery('#amount_of_tickets_" . $i . "').val('" . $amount_of_tickets . "');
                                        jQuery('#event_type_" . $i . "').val('" . $event_type . "');
                                        jQuery('#event_name_" . $i . "').val('" . $event_name . "');
                                        jQuery('#event_location_" . $i . "').val('" . $location . "');
                                        jQuery('#event_start_datepicker_" . $i . "').val('" . $start_date . "');
                                        jQuery('#event_start_timepicker_" . $i . "').val('" . $start_time . "');

        
                                        jQuery('#to_datepicker_" . $i . "').val('" . substr($prize_draw_to, 0, -5) . "');
                                        jQuery('#to_timepicker_" . $i . "').val('" . substr($prize_draw_to, -5, 5) . "');
        
                                        jQuery('#prize_draw_email_" . $i . "').val('" . $email . "');
                                        ";

                }

            }


        }
        //endregion

        //region tinymce

        tinymce_editor_js();

        //endregion


        //region Listeners, initialisations & File uploaders

        ?>


        let file_uploader_captions = <?php echo file_uploader_captions();?>;

        file_uploader_captions['feedback'] = "<?php echo $GLOBALS['Strings']['emptyTextThumbnail'] ?>";

        var thumnail_jquery = jQuery('#thumbnail').fileuploader({

            <?php
            if ($edit_Post) {
                $atmd = wp_get_attachment_metadata(get_post_thumbnail_id($edit_Post->ID));

                $mime = $atmd['sizes']['medium']['mime-type'];
                echo "files:[{
                        name: 'Vorschaubild', // file name
                        size: '0', // file size in bytes
                        type: '" . $mime . "', // file MIME type,
                        file: '" . home_url() . "/wp-content/uploads/" . $atmd["file"] . "',
                            data: {
        popup: false, // remove the popup for this file (optional)
        your_own_attribute: 'your own value'
    }

                }],";
            }

            ?>

            captions: file_uploader_captions,
            enableApi: true,
            limit: 1,
            maxSize: 1000,
            theme: 'default',
            dragDrop: {
                container: '#thumbnailCont',
            },
            addMore: false,
            onFileRead: function (item, listEl, parentEl, newInputEl, inputEl) {
                var api = jQuery.fileuploader.getInstance(thumnail_jquery);
                convertHeicToJpg(jQuery('input[name="thumbnail"]').get(0), api, jQuery('#thumbnailCont'), 0);
            },
        });
        file_uploader_captions['feedback'] = "<?php echo $GLOBALS['Strings']['emptyTextGallery'] ?>";
        var gallery_jquery = (jQuery('#gallery').fileuploader({

                <?php
                if ($edit_Post && $edit_Post_meta['media_type'][0] == 'gallery') {
                    $galleryIds = json_decode($edit_Post_meta['gallery_attachment_id'][0]);
                    if (sizeof($galleryIds) > 0) {
                        echo "files:[";
                        foreach ($galleryIds as $imgId) {
                            if (!empty($imgId)) {

                                $atmd = wp_get_attachment_metadata($imgId);

                                $name = explode('/', $atmd["file"])[2];
                                $mime = get_post_mime_type($imgId);
                                echo "{name: '" . $name . "', // file name
                                          size: '0', // file size in bytes
                                          type: '" . $mime . "', // file MIME type,
                                          file: 'wp-content/uploads/" . $atmd["file"] . "',
                                          data: {
                                            listProps: {id: '" . $imgId . "'},
                                            thumbnail: '" . wp_get_attachment_thumb_url($imgId) . "'
                                          }
                                          },";

                            }
                        }
                        echo "],";
                    }
                }

                ?>
                captions: file_uploader_captions,
                limit: 100,
                maxSize: 1000,
                enableApi: true,
                theme: 'default',
                addMore: true,
                onFileRead: function (item, listEl, parentEl, newInputEl, inputEl) {

                    var api = jQuery.fileuploader.getInstance(gallery_jquery);
                    convertHeicToJpg(jQuery('#gallery').get(0), api, jQuery('#galleryCont'), 1);
                },
                thumbnails:
                    {
                        onItemShow: function (item) {
                            // add sorter button to the item html
                            item.html.find('.fileuploader-action-remove').before('<button type="button" class="fileuploader-action fileuploader-action-sort" title="Sort"><i class="fileuploader-icon-sort"></i></button>');
                        }
                    }
                ,
                sorter: {
                    selectorExclude: null,
                    placeholder: null,
                    scrollContainer: window,
                    onSort: function (list, listEl, parentEl, newInputEl, inputEl) {
                        // onSort callback
                    }
                },
                dragDrop: {
                    container: '#galleryCont',
                },
            }
        ));

        file_uploader_captions['feedback'] = "<?php echo $GLOBALS['Strings']['emptyTextDocument'] ?>";
        jQuery('#document').fileuploader({

                <?php
                if ($edit_Post) {
                    $documentIds = json_decode($edit_Post_meta['documents_attachment_id'][0] ?? '[]');
                    if (sizeof($documentIds) > 0) {
                        echo "files:[";
                        foreach ($documentIds as $dcmId) {
                            if (!empty($dcmId)) {

                                $atmd = wp_get_attachment_metadata($dcmId);

                                $name = explode('/', $atmd["file"])[2];
                                $mime = get_post_mime_type($dcmId);
                                echo "{name: '" . get_the_title($dcmId) . "', // file name
                                          size: '0', // file size in bytes
                                          type: '" . $mime . "', // file MIME type,
                                          file: '" . wp_get_attachment_url($dcmId) . "',
                                          data: {
                                            listProps: {id: '" . $dcmId . "'},
                                            thumbnail: '" . home_url() . '/' . get_Client_Image_Dir() . 'pdf.png' . "'
                                          }
                                          },";

                            }
                        }
                        echo "],";
                    }
                }

                ?>
                captions: file_uploader_captions,
                limit: 100,
                maxSize: 1000,
                theme: 'default',
                addMore: true,
                thumbnails:
                    {
                        onItemShow: function (item) {
                            // add sorter button to the item html
                            item.html.find('.fileuploader-action-remove').before('<button type="button" class="fileuploader-action fileuploader-action-sort" title="Sort"><i class="fileuploader-icon-sort"></i></button>');
                        }
                    }
                ,
                sorter: {
                    selectorExclude: null,
                    placeholder: null,
                    scrollContainer: window,
                    onSort: function (list, listEl, parentEl, newInputEl, inputEl) {
                        // onSort callback
                    }
                }, dragDrop: {
                    container: '#DocumentCont',
                },
            }
        );


        file_uploader_captions['feedback'] = "<?php echo $GLOBALS['Strings']['emptyTextVideo']; ?>";

        jQuery('#videoFile').fileuploader({

            <?php
            if ($edit_Post) {
                $atmd = wp_get_attachment_metadata(get_post_thumbnail_id($edit_Post->ID));

                $mime = $atmd['sizes']['medium']['mime-type'];
                echo "files:[{
                        name: 'Bestehendes Video', // file name
                        size: '0', // file size in bytes
                        type: '" . $mime . "', // file MIME type,
                        file: '" . home_url() . "/wp-content/uploads/" . $atmd["file"] . "',
                            data: {
        popup: false, // remove the popup for this file (optional)
        your_own_attribute: 'your own value'
    }
                        

                }],";
            }

            ?>

            captions: file_uploader_captions,
            enableApi: true,
            limit: 1,
            maxSize: 1000,
            theme: 'default',
            dragDrop: {
                container: '#videoCont',
            },
            addMore: false
        });


        file_uploader_captions['feedback'] = "<?php echo $GLOBALS['Strings']['emptyTextAudio']; ?>";

        jQuery('#audio').fileuploader({

            <?php
            if ($edit_Post) {
                $atmd = wp_get_attachment_metadata(get_post_thumbnail_id($edit_Post->ID));

                $mime = $atmd['sizes']['medium']['mime-type'];
                echo "files:[{
                        name: 'Bestehende Audio-Datei', // file name
                        size: '0', // file size in bytes
                        type: '" . $mime . "', // file MIME type,
                        file: '" . home_url() . "/wp-content/uploads/" . $atmd["file"] . "',
                            data: {
        popup: false, // remove the popup for this file (optional)
        your_own_attribute: 'your own value'
    }       
                }],";
            }

            ?>

            captions: file_uploader_captions,
            enableApi: true,
            limit: 1,
            maxSize: 1000,
            theme: 'default',
            dragDrop: {
                container: '#AudioCont',
            },
            addMore: false
        });


        jQuery(document).on("keyup keypress", ":input:not(textarea)", function (event) {
            var keyCode = event.keyCode || event.which;
            return keyCode !== 13;
        });

        showLoading(false);

        var f_duration = 0; //store duration

        var obUrlAudio;
        document.getElementById('audio').addEventListener('change', function (e) {
            var file = e.currentTarget.files[0]; //check file extension for audio/video type

            if (file.name.match(/\.(avi|mp3|mpeg|ogg)$/i)) {
                obUrlAudio = URL.createObjectURL(file);
                document.getElementById('hidden_Audio_output').setAttribute('src', obUrlAudio);
            }
        });

        document.getElementById('hidden_Audio_output').addEventListener('canplaythrough', function (e) {
            //add duration in the input field #f_du
            f_duration = Math.round(e.currentTarget.duration);
            document.getElementById('duration').value = f_duration;
            URL.revokeObjectURL(obUrlAudio);
        }); //when select a file, create an ObjectURL with the file and add it in the #audio element


        (function ($) {
            $(document).ready(function () {
                $('#from_datepicker').datepicker();
                $('#to_datepicker_0').datepicker();
                $('#event_start_datepicker_0').datepicker();

                $('#post_later_date').datepicker();

            });
        })(jQuery);

        let mediaSelect = document.getElementById("mediaSelect");

        let edit_Post = <?php if ($edit_Post) {
            echo 'true';
        } else {
            echo 'false';
        }?>;

        let title = document.getElementById("titleCont");
        let subhead = document.getElementById("subheadingCont");
        let content = document.getElementById("contentCont");
        let tags = document.getElementById("tagsCont");
        let category = document.getElementById("categoryCont");
        let cat = document.getElementById("cat");

        let documentCont = document.getElementById("DocumentCont");
        let video = document.getElementById("videoCont");
        let thumbnail = document.getElementById("thumbnailCont");
        let thumbnail_label = document.getElementById("thumbnail_label");
        let gallery = document.getElementById("galleryCont");
        let audio = document.getElementById("AudioCont");
        let prize_draw_from = document.getElementById("prize_draw_from");
        let prize_draw_to = document.getElementById("prize_draw_to");
        let prize_draw_email = document.getElementById("prize_draw_email_Cont");
        let prize_draw_template = document.getElementById("prize_draw_template");
        let additional_prize_draw_inputs = document.getElementById("additional_prize_draw_inputs");

        let allow_comments = document.getElementById("allow_comments_Cont");
        let post_later = document.getElementById("post_later");
        let post_later_cont = document.getElementById("post_later_Cont");
        let post_later_date_cont = document.getElementById("post_later_date_cont");
        let post_later_time_cont = document.getElementById("post_later_time_cont");
        let notify_cont = document.getElementById("notify_Cont");
        let notify = document.getElementById("notify");
        let post_invisible_cont = document.getElementById("post_invisible_cont");

        let author_cont = document.getElementById("author_Cont");
        let author_me = document.getElementById("author_me");
        let author_id_Cont = document.getElementById("author_id_cont");
        let author_id = document.getElementById("author_id");

        let uploading_info = document.getElementById('uploading_info');

        let submit = document.getElementById("sumbitButton");


        title.style.display = "none";
        subhead.style.display = "none";
        content.style.display = "none";
        category.style.display = "none";
        tags.style.display = "none";
        video.style.display = "none";
        thumbnail.style.display = "none";
        gallery.style.display = "none";
        documentCont.style.display = "none";
        audio.style.display = "none";
        submit.style.display = "none";
        /*prize_draw_from.style.display = "none";
        prize_draw_to.style.display = "none";
        prize_draw_email.style.display = "none";*/
        additional_prize_draw_inputs.style.display = "none";


        mediaSelect.onchange = mediaChanged;

        if (author_me != null) {
            author_me.addEventListener('change', function (event) {
                if (event.target.checked) {
                    safeHideSow(author_id_Cont, false);
                    safeDisable(author_id, true);
                } else {
                    safeHideSow(author_id_Cont, true);
                    safeDisable(author_id, false);
                }
            });
        }

        if (post_later != null) {
            post_later.addEventListener('change', function (event) {
                if (event.target.checked) {
                    safeHideSow(post_later_date_cont, true);
                    safeHideSow(post_later_time_cont, true);

                    safeDisable(post_later_date_cont, false);
                    safeDisable(post_later_time_cont, false);


                } else {
                    safeHideSow(post_later_date_cont, false);
                    safeHideSow(post_later_time_cont, false);

                    safeDisable(post_later_date_cont, true);
                    safeDisable(post_later_time_cont, true);
                }
            });
        }

        <?php
        //endregion

        //region category change listener

        ?>

        if (cat != null) {
            cat.addEventListener('change', function (event) {
                var drafts = (this.options[this.selectedIndex].dataset.drafts) == 'true';

                update_uploading_Info();

                if (!drafts || mediaSelect.value == 'prize_draw') {
                    safeHideSow(post_later_cont, false);
                } else {
                    safeHideSow(post_later_cont, true);
                }


                var comments = (this.options[this.selectedIndex].dataset.comments);
                if (comments == 0) {
                    safeHideSow(allow_comments, true);
                } else {
                    safeHideSow(allow_comments, false);
                }

                var media = (this.options[this.selectedIndex].dataset.cat_forces_media);
                if (media != '') {
                    mediaSelect.value = media;
                    mediaSelect.classList.add('disabled');
                    mediaChanged(false);
                } else {
                    mediaSelect.classList.remove('disabled');
                }

            });
        }

        //used when editing
        if (mediaSelect.value !== 'none') {
            mediaChanged();
        }

        //endregion

        //region helper functions
        // todo use this everywhere
        function safeHideSow(elemt, show) {
            if (elemt == null) {
                return;
            }

            if (show) {
                elemt.style.display = 'block';
            } else {
                elemt.style.display = 'none';
            }
        }

        function safeDisable(elemt, val) {
            if (elemt == null) {
                return;
            }

            elemt.disabled = val;
        }

        var convertingfiles = [[], []]
        var newfiles = [];
        var awaitingImages = 0;

        async function convertHeicToJpg(input, api, file_input_container, convertingfiles_index) {
            var files = api.getFiles();
            var index = 0;
            var length = files.length;
            for (index = 0; index < length; index++) {
                var fileName = files[index].name;
                var fileNameExt = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase();
                if (fileNameExt === "heic" && !convertingfiles[convertingfiles_index].includes(fileName)) {

                    convertingfiles[convertingfiles_index].push(fileName);

                    if (awaitingImages === 0) {
                        jQuery("#sumbitButton").prop("disabled", true);
                        file_input_container.addClass("converting");
                    }
                    awaitingImages++;
                    var blob = files[index].file; //ev.target.files[0];
                    //item = api.getFiles()[index];
                    var item = files[index];


                    var resultBlob = await heic2any({
                        blob: blob,
                        toType: "image/jpg",
                    });

                    var url = URL.createObjectURL(resultBlob);
                    //adding converted picture to the original <input type="file">
                    let fileInputElement = jQuery(input)[0];
                    let container = new DataTransfer();
                    let file = new File([resultBlob], fileName.substr(0, fileName.lastIndexOf('.')) + ".jpg", {
                        type: "image/jpeg",
                        lastModified: new Date().getTime()
                    });
                    container.items.add(file);


                    newfiles.push({file: file, item: item});
                    if (awaitingImages == 1) {
                        var b = new ClipboardEvent("").clipboardData || new DataTransfer()
                        for (var i = 0, len = newfiles.length; i < len; i++) {
                            b.items.add(newfiles[i].file);
                            let =
                            fileName = newfiles[i].item.name;

                            api.update(newfiles[i].item, {
                                name: newfiles[i].file.name,
                                type: 'image/jpeg',
                                size: newfiles[i].file.size,
                                file: newfiles[i].file
                            });
                            var cf_index = convertingfiles[convertingfiles_index].indexOf(fileName);
                            if (cf_index !== -1) {
                                convertingfiles[convertingfiles_index].splice(cf_index, 1);
                            }
                        }
                        input.files = b.files


                        jQuery("#sumbitButton").prop("disabled", false);
                        file_input_container.removeClass("converting");
                    }
                    awaitingImages--;
                }

            }
            return null;
        }

        <?php
        //endregion

        //region update uploading Info
        ?>
        function update_uploading_Info() {

            let text = "";

            let data = cat.options[cat.selectedIndex].dataset;

            var drafts = data.drafts == 'true';
            var aan = data.admin_activation == 'true';
            var notifications = data.notifications;
            var comments = data.comments;

            if (comments == 0) {
                safeHideSow(allow_comments, true);
            } else {
                safeHideSow(allow_comments, false);
            }

            if (edit_Post) {
                <?php
                global $current_user;
                if (in_array('business_partner', $current_user->roles)) {
                    echo 'text ="Ihre Änderungen am Beitrag müssen von einem Administrator freigegeben werden, um für alle Mitarbeiter sichtbar zu sein. Die Freigabe kann über die Schaltfläche im Beitrag beantragt werden."';
                }
                ?>
            } else {
                if (drafts) {
                    text = "Ihr Beitrag wird zuerst als Entwurf gespeichert";
                    if (aan) {
                        text += " und muss von einem Administrator freigegeben werden, um für alle Mitarbeiter sichtbar zu sein. Die Freigabe kann über die Schaltfläche im Beitrag beantragt werden. ";
                    } else {
                        text += ". Bitte überprüfen Sie diesen Entwurf und aktivieren ihn anschließend über die Schaltfläche im Beitrag, damit er für alle Mitarbeiter sichtbar wird. ";
                    }
                } else {
                    text = "Der Beitrag muss nicht aktiviert werden, sondern wird direkt veröffentlicht. ";
                }

                if (notifications == 'ALWAYS') {
                    text += "Bei der Veröffentlichung des Beitrags werden automatisch alle Nutzer entsprechend benachrichtigt.";
                } else if (notifications == 'NEVER') {
                    text += "Bei der Veröffentlichung des Beitrags werden keine Benachrichtigungen verschickt.";
                } else {
                    text += "Benachrichtigungen können von dazu berechtigten Nutzern im Post angestoßen werden.";
                }
            }

            uploading_info.innerHTML = text;
            if (text == "") {
                uploading_info.style.display = 'none';
            } else {
                uploading_info.style.display = 'block';
            }
        }
        <?php
        //endregion

        //region mediachanged
        ?>
        function mediaChanged(trigger_change_cat = true) {

            var value = (mediaSelect.value || mediaSelect.options[mediaSelect.selectedIndex].value);

            mediaSelect.options[0].hidden = true;
            mediaSelect.options[0].disabled = true;

            video.style.display = "none";
            gallery.style.display = "none";
            audio.style.display = "none";
            title.style.display = "block";
            subhead.style.display = "block";


            document.getElementById('createPostCancelButton').style.marginLeft = null;
            submit.style.display = "inline-block";
            thumbnail.style.display = "block";
            allow_comments.style.display = "block";
            if (post_invisible_cont != null) {
            post_invisible_cont.style.display = "block";
            }
            if (author_cont != null) {
                author_cont.style.display = "block";
            }

            var cat = document.getElementById('cat');


            <?php


            /*if (!$restrictedMode) {*/
            echo 'category.style.display = "block";';
            /*  }*/
            ?>



            // As prize draws use a completely different layout than other post-types, they are
            // distinguished before the switch-case to keep code slim
            if (value === 'prize_draw') {

                content.style.display = "none";
                tags.style.display = "none";

                additional_prize_draw_inputs.style.display = "block";

                jQuery(".date_time_picker").each(function (i, elem) {
                    elem.required = true;
                });

                if (fileuploader == null) {
                    fileuploader = jQuery.fileuploader.getInstance(jQuery('#thumbnail'));
                }

                fileuploader.reset();

                if (!edit_Post) {
                    change_prize_draw_template(0);
                }

            } else {

                if (fileuploader == null) {
                    fileuploader = jQuery.fileuploader.getInstance(jQuery('#thumbnail'));
                }


                <?php
                if (!$edit_Post) {
                    echo 'if (fileuploader.getUploadedFiles().length == 0) {
                    fileuploader.reset();}';
                }
                ?>


                content.style.display = "block";
                tags.style.display = "block";
                safeHideSow(documentCont, true);
                additional_prize_draw_inputs.style.display = "none";

                jQuery(".date_time_picker").each(function (i, elem) {
                    elem.required = false;
                });

                switch (value) {
                    case "video":
                        video.style.display = "block";
                        break;

                    case "audio":
                        audio.style.display = "block";
                        break;

                    case "article":
                        break;

                    case "gallery":
                        gallery.style.display = "block";
                        break;

                <?php custom_post_media_changed_js() ?>
                }

            }

            // cat handling

            let cat_media = {};

            <?php
            $mediatypes = get_mediatypes();

            foreach ($mediatypes as $mt) {
                $category_media = get_category_by_media_type($mt);
                if (!is_null($category_media)) {
                    echo 'cat_media["' . $mt . '"]  = ' . $category_media . ';';
                }
            }
            ?>


            for (let mt in cat_media) {

                if (cat_media[mt] <= 0) {
                    continue;
                }

                if (mt == value) {
                    cat.value = cat_media[mt];
                    cat.options[cat.selectedIndex].disabled = false;
                    cat.options[cat.selectedIndex].hidden = false;
                    category.style.display = "none";
                    continue
                }

                if (cat_media[value] == cat_media[mt]) {
                    continue;
                }

                for (let cat_index = 0; cat_index < cat.length; cat_index++) {
                    if (cat.options[cat_index].value === cat_media[mt]) {
                        cat.options[cat_index].disabled = true;
                        cat.options[cat_index].hidden = true;
                        if (cat.selectedIndex == cat_index) {
                            cat.selectedIndex = 0;
                        }
                    }
                }
                ;
            }
            ;


            if (trigger_change_cat) {

                var event = new Event('change');
                cat.dispatchEvent(event);
            }

        }


        <?php
        if (!$edit_Post) {
            echo 'set_datepicker_date("event_start_", 7,"_0");
                    set_datepicker_date("to_", 7,"_0");
                    set_datepicker_date("from_", 3);';
        }

        ?>

        document.getElementById('prize_draw_starting_now').addEventListener('change', function (event) {
            if (event.target.checked) {
                document.getElementById('from_datepicker').value = "Nach Freigabe";
                document.getElementById('from_timepicker').value = "Nach Freigabe";

                document.getElementById('from_datepicker').classList.remove('disabled');
                document.getElementById('from_datepicker').classList.add('disabled');
                document.getElementById('from_timepicker').classList.remove('disabled');
                document.getElementById('from_timepicker').classList.add('disabled');

                document.getElementById('from_datepicker_lable').classList.remove('disabled');
                document.getElementById('from_datepicker_lable').classList.add('disabled');
                document.getElementById('from_timepicker_lable').classList.remove('disabled');
                document.getElementById('from_timepicker_lable').classList.add('disabled');
            } else {
                set_datepicker_date("from_", 0, '');
                document.getElementById('from_timepicker').value = "12:00";
                document.getElementById('from_datepicker_lable').classList.remove('disabled');
                document.getElementById('from_timepicker_lable').classList.remove('disabled');

                document.getElementById('from_datepicker').classList.remove('disabled');
                document.getElementById('from_timepicker').classList.remove('disabled');

            }
        });

        document.getElementById('postbox').style.display = 'block';
    })
    ;

    <?php
    //endregion


    //region prize-draws
    ?>

    var fileuploader = null;

    //fill in information from prize draw templates
    function change_prize_draw_template(index) {

        let prize_draw_template = document.getElementById("prize_draw_template_" + index);

        var event_type = document.getElementById('event_type_' + index);
        var event_name = document.getElementById('event_name_' + index);
        var event_location = document.getElementById('event_location_' + index);
        var template_thumbnail = document.getElementById('template_thumbnail');

        if (!prize_draw_template) {
            return;
        }

        var type = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-type');
        var type_list = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-type-list');
        var location = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-location');
        var location_list = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-location-list');

        var type_placeholder = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-type-placeholder');
        var location_placeholder = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-location-placeholder');
        var name_placeholder = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-name-placeholder');


        var name = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-name');
        var thumbnail_url = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-thumbnail');
        var thumbnail_id = prize_draw_template.options[prize_draw_template.selectedIndex].getAttribute('data-thumbnail-id');


        //type
        if (type) {
            event_type.value = type;
        } else {
            event_type.value = '';
            if (type_list) {
                event_type.setAttribute('list', type_list);
            }
        }
        if (type_placeholder) {
            event_type.placeholder = type_placeholder;
        } else if (type) {
            event_type.placeholder = type;
        }


        //location
        if (location) {
            event_location.value = location;
        } else {
            event_location.value = '';
            if (location_list) {
                event_location.setAttribute('list', location_list);
            }
        }
        if (location_placeholder) {
            event_location.placeholder = location_placeholder;
        } else if (location) {
            event_location.placeholder = location;
        }


        if (name) {
            event_name.value = name;
        } else {
            event_name.value = '';
        }

        if (name_placeholder) {
            event_name.placeholder = name_placeholder;
        } else if (name) {
            event_name.placeholder = name;
        }

        if (fileuploader == null) {
            fileuploader = jQuery.fileuploader.getInstance(jQuery('#thumbnail'));
        }

        fileuploader.reset();

        if (thumbnail_url && thumbnail_id) {
            template_thumbnail.value = thumbnail_id;

            thumbnail_url = "<?php echo home_url();?>/" + thumbnail_url;

            fileuploader.append({
                name: 'Vorlagen Vorschaubild', // file name
                size: 1024, // file size in bytes
                type: 'image/png', // file MIME type
                file: thumbnail_url // file path
            });


        } else {
            template_thumbnail.value = '';
        }

    }


    function remove_prize_draw_event_form(index) {
        jQuery("#prize_draw_event_form_" + index).remove();
    }

    function add_prize_draw_event_form() {

        // get the last event_form
        var event_form = jQuery(".prize_draw_event_form").last();

        // Read the Number from its ID (i.e: 3 from "prize_draw_event_form_3")
        // And increment that number by 1
        var num = parseInt(event_form.prop("id").match(/\d+/g), 10) + 1;


        // Clone it and assign the new ID
        let clone = event_form.clone();
        clone[0].id = clone[0].id.substr(0, clone[0].id.lastIndexOf("_")) + '_' + num;

        // Iterate over all elements with assigned Ids and change all their numbers to the new one
        let items = clone.find('*[id]');
        items.each(function (i) {
            let item = items[i];

            item.id = item.id.substr(0, item.id.lastIndexOf("_")) + '_' + num;
        });


        items = clone.find('*[for]');
        items.each(function (i) {
            let item = items[i];
            item.setAttribute('for', item.getAttribute('for').substr(0, item.getAttribute('for').lastIndexOf("_")) + '_' + num);
        });

        //get value of previous dropdowns and apply to new fields
        clone.find('#prize_draw_template_' + num).val(event_form.find('#prize_draw_template_' + (num - 1)).val());
        clone.find('#amount_of_tickets_' + num).val(event_form.find('#amount_of_tickets_' + (num - 1)).val());

        // Finally insert the cloned form
        clone.appendTo("#prize_draw_event_form_containter");


        jQuery('#to_datepicker_' + num).removeClass('hasDatepicker').datepicker();
        jQuery('#event_start_datepicker_' + num).removeClass('hasDatepicker').datepicker();

    }
    <?php
    //endregion

    //region time functions
    ?>

    function validate_time(inputField) {
        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField.value);

        if (!isValid) {
            inputField.style.backgroundColor = '#fba';
        } else {
            inputField.style.backgroundColor = '#fff';
        }

        return isValid;
    }

    function validate_date(inputField) {
        var isValid = /^(0?[1-9]|[12][0-9]|3[01])[.](0?[1-9]|1[012])[.]\d{4}$/.test(inputField.value);

        if (!isValid) {
            inputField.style.backgroundColor = '#fba';
        } else {
            inputField.style.backgroundColor = '#fff';
        }

        return isValid;
    }

    function set_datepicker_date(prefix) {
        var additionalDays = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
        var postfix = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : "";
        var date = new Date();
        date.setDate(date.getDate() + additionalDays);
        var dd = String(date.getDate()).padStart(2, '0');
        var mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!

        var yyyy = date.getFullYear();
        date = dd + '.' + mm + '.' + yyyy;
        document.getElementById(prefix + 'datepicker' + postfix).value = date;
    }

    <?php
    //endregion

    ?>


</script>