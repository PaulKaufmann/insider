<?php
/**
 * Template Name: tescht
 */


genesis();
//update_option('sending_emails', 'true');
echo get_option('sending_emails');

//rename_user('123','S123','Stest1@muxd.de','Sf','Sl');
//rename_user('S123','123','test1@muxd.de','f','l');
//rename_user('123',false,'test1@muxd.de','f','l');
//rename_user('S126','126','test4@muxd.de','f4','l4');

/**/


?>
 <iframe width="960" height="540" src="https://s-oberlandler.de" title="W3Schools Free Online Web Tutorials"></iframe> 
 <br>
    <!-- <iframe width="960" height="540" src="https://sparkassen-mediacenter.de/mediacenter/mediathek/player/extern?id=0_22sb35dd&blz=73350000&autoplay=false" frameborder="0" scrolling="no" allowfullscreen></iframe> -->
    <div class="postbox visible_postbox">
        <h2>Mehrere Benutzer bearbeiten</h2>
        <form id="bulk_user_creating" name="bulk_user_creating" method="post" autocomplete="off">

            <?php wp_nonce_field('inseider-bulk-user-edit', 'inseider-bulk-user-edit-nonce'); ?>

            <label for="bulkEditInput">Neue Daten einfügen und kurzes Stoßgebet amk, vorher die IDs + Emails zur
                Sicherheit auf duplicates prüfen</label>
            <br>
            <br>
            <textarea oninput="auto_grow(this)" id="bulkEditInput" tabindex="3" name="bulkEditInput" cols="50"
                      rows="6"
                      placeholder="Entsprechende Zeilen aus Excel kopieren und hier einfügen"></textarea>
            <br>
            <br>
            <button class="spk_wide_button" name="submit" value="bulkEditUsers" type="submit">Ab gehts!
            </button>
        </form>
    </div>
<?php


if ($_POST['submit'] == 'bulkEditUsers') {
    if (!wp_verify_nonce($_POST['inseider-bulk-user-edit-nonce'], 'inseider-bulk-user-edit')) {
        $errors[] = 'Das Formular ist Fehlerhaft, bitte versuchen Sie es erneut';
        dbg($errors);
        return $errors;
    }

    $inpur_raw = sanitize_textarea_field($_POST['bulkEditInput']);
    global $wp_roles;

    $users_raw = explode(PHP_EOL, $inpur_raw);
    $users = array();

    foreach ($users_raw as $user_raw) {
        $tmp_user = preg_split('/\t+/', $user_raw);

        $old_login_name = $tmp_user[0];
        $new_login_name = $tmp_user[1];
        $new_firstname = $tmp_user[2];
        $new_lastname = $tmp_user[3];
        $new_email = $tmp_user[4];

        if (is_null($old_login_name) || is_null($new_login_name) || is_null($new_firstname) || is_null($new_lastname) || is_null($new_email)) {
            $errors[] = 'Mindestens ein Feld ist leer oder konnte nicht richtig eingelesen werden, überprüfen Sie die Eingabe.';
            return $errors;
        }


        $user_login = get_user_by('login', $old_login_name);
        $user_mail = get_user_by_email($new_email);

        if (!is_email($new_email)) {
            $errors[] = $new_email . ' ist keine valide E-Mail-Adresse.';
        }

        if (empty($user_login)) {
            $errors[] = 'Der Nutzer mit dieser Personalnummer existiert nicht: ' . $old_login_name;
            continue;
        }

        if (!empty($user_mail) && !empty($user_login) && $user_login->ID != $user_mail->ID) {
            $errors[] = 'User conflict ' . $old_login_name . ' ' . $new_email;
            continue;
        }


        //check for duplicates
        foreach ($users as $user) {
            if ($user[0] === $old_login_name) {
                $errors[] = "Die Personalnummer $old_login_name existiert mehrmals in der Eingabe.";
            }

            if ($user[1] === $new_login_name) {
                $errors[] = "Die Personalnummer $new_login_name existiert mehrmals in der Eingabe.";
            }

            if ($user[4] === $new_email) {
                $errors[] = "Die E-Mail-Adresse $new_email existiert mehrmals in der Eingabe.";
            }
        }
        $users[] = array($old_login_name, $new_login_name, $new_firstname, $new_lastname, $new_email, $user_login->user_email);
    }

    if (empty($errors)) {

        $is_final = true;


        echo '
                <table style="width:100%">
                  <tr>
                    <th>Alter Login</th>
                    <th>Neuer Login</th>
                    <th>Vorname</th>
                    <th>Nachname</th>
                    <th>Neue Email</th>
                    <th>Alte Email</th>
                  </tr>';
        foreach ($users as $user) {


            if ($is_final) {
                rename_user($user[0],$user[1],$user[4],$user[2],$user[3]);
            }

            echo "
                    <tr>
                        <td>$user[0]</td>
                        <td>$user[1]</td>
                        <td>$user[2]</td>
                        <td>$user[3]</td>
                        <td>$user[4]</td>
                        <td>$user[5]</td>
                      </tr>";
        }
        echo '</table>';
    } else {
        dbg($errors, true);
    }
}

//delete_user_meta(get_current_user_id(),"onesignal_player_ids");
function video_status_check($id)
{
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_URL, 'https://api.vimeo.com/videos/' . $id);
    curl_setopt($handle, CURLOPT_HTTPGET, 1);
    curl_setopt($handle, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . get_vimeo_access_token(),
        'Accept: application/vnd.vimeo.*+json;version=3.4'
    ));

    $response = curl_exec($handle);
    curl_close($handle);

// Decode the response to get the thumbnailurl
    $responseData = json_decode($response, TRUE);
    $thumbnailPostUrl = $responseData['pictures']['sizes'][3]['link'];

    $url_components = parse_url($responseData['player_embed_url']);

// Display result
    dbg($id . '?' . $url_components['query']);


    $responseData['embed'] = '';
    dbg($responseData);


    if ($responseData['transcode']['status'] !== 'complete' || $responseData['pictures']['type'] === 'default') {
        // wait again
        dbg('wait again');
        return;
    }
    dbg('continue');

}


//unfinished but working
function rename_user($old_login_name, $new_login_name = false, $new_email = false, $new_firstname = false, $new_lastname = false)
{
    global $wpdb;

    $user = get_user_by('login', $old_login_name);

    if(!$user){
        dbg("No user found for $old_login_name");
        return false;
    }

    $data = array();
    $name_data=array();

    if ($new_login_name) {
        $data ['user_nicename'] = $new_login_name;
        $data ['user_login'] = $new_login_name;
    }

    if ($new_email) {
        $name_data ['user_email'] = $new_email;
    }

    if ($new_firstname) {
        $name_data ['first_name'] = $new_firstname;
    }

    if ($new_lastname) {
        $name_data ['last_name'] = $new_lastname;
    }

    if(! empty($data)){
        dbg($wpdb->update($wpdb->users, $data, array('ID' => $user->ID)));
    }

    if(! empty($name_data)) {
        $name_data['ID']=$user->ID;
        dbg( wp_update_user($name_data));
    }

    return 1;



/*    $count = 0;
    foreach ($users as $user) {

        if (strpos($user->user_email, 'cup-n.de')) {

            $wpdb->update($wpdb->users, array('user_login' => 'paul4'), array('ID' => $user->ID));

            wp_update_user(array(
                'ID' => $user->ID,
                //         'user_email' => 'Paul.kaufmann@cup-n.de',
                'user_email' => 'Paulkaufmann@hotmail.de',
                'user_nicename' => 'paul41'
            ));
            dbg(get_user_by('ID', $user->ID));

        }


    }*/
}


function show_statistics()
{


    $users = get_users();
    $all = sizeof($users);

    $total_views = 0;
    $most_views = 0;
    $least_views = 0;
    $avg_views = 0;
    $total_comments = 0;
    $most_comments = 0;
    $least_comments = -1;
    $total_posts = 0;

    $args = array(
        'numberposts' => -1,

    );

    $posts = get_posts($args);
    $views_temp = 0;
    foreach ($posts as $p) {
        $views_temp = get_post_meta($p->ID, 'view_count', true);
        $total_views += $views_temp;
        $total_posts++;

        if ($least_views == 0 || $views_temp < $least_views) {
            $least_views = $views_temp;
        }

        if ($most_views < $views_temp) {
            $most_views = $views_temp;
        }

        $comments = get_comments_number($p->ID);
        $total_comments += $comments;


        if ($most_comments < $comments) {
            $most_comments = $comments;
        }
    }
    $avg_views = $total_views / $total_posts;
    $avg_comments = $total_comments / $total_posts;

    echo "$all ?? $total_posts $total_views $least_views $most_views $avg_views $total_comments $most_comments $avg_comments";
}


function show_all_post_meta($post_id)
{
    dbg(get_post_meta($post_id), true);
}

function show_communication_settings()
{
    dbg('ACHTUNG erstes Array: werte für private adressen, zweites für spk');
    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");
    $count = 0;
    foreach ($users as $user) {

        $old_values = get_user_meta($user->ID, 'notifications_categories', true);

        if ($old_values) {
            $count++;
            $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);

            echo 'main: ' . $user->user_email . '<br>';
            echo 'side: ' . $custom_mail;

            dbg($old_values);
            echo "<br>";
        }

    }
    dbg('users shown: ' . $count);

}

//all users with adjusted communication settings will have a specific value set, usefull for new categories
function edit_communication_settings($cat_id, $value)
{
    dbg('ACHTUNG erstes Array: werte für private adressen, zweites für spk');
    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");
    $count = 0;
    foreach ($users as $user) {

        $old_values = get_user_meta($user->ID, 'notifications_categories', true);

        if ($old_values) {
            $count++;
            $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);

            echo 'main: ' . $user->user_email . '<br>';
            echo 'side: ' . $custom_mail;

            dbg($old_values);
            echo "<br>";


            $old_values[1][$cat_id] = $value;

            update_user_meta($user->ID, 'notifications_categories', $old_values);

            dbg($old_values);

        }

    }
    dbg('users adjusted: ' . $count);

}

function test_send_mail($post_id)
{
    global $wpdb;

    $post_categories = get_the_category($post_id);

    $users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");
    $count1 = 0;
    $count2 = 0;
    $count3 = 0;
    $count4 = 0;
    $either = false;
    foreach ($users as $user) {
        $count1++;
        $category_matrix = get_user_meta($user->ID, 'notifications_categories');
        if (empty($category_matrix)) {
            $send_main = true;
            $send_custom = true;
        } else {
            $category_matrix = $category_matrix[0];
            if (is_null($category_matrix[1]) || is_null($category_matrix[1][$post_categories[0]->term_id])) {
                $send_main = true;
            } else {
                $send_main = $category_matrix[1][$post_categories[0]->term_id];
            }

            if (is_null($category_matrix[0]) || is_null($category_matrix[0][$post_categories[0]->term_id])) {
                $send_custom = true;
            } else {
                $send_custom = $category_matrix[0][$post_categories[0]->term_id];
            }
        }

        $mail = $user->user_email;

        $notifications_enabled_for_posts = get_user_meta($user->ID, 'notifications_posts', true) != 'false';
        if ($notifications_enabled_for_posts && $send_main) {
            $either = true;
            $count2++;
            dbg('would send email to main : ' . $mail);
        }

        $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);
        if ($custom_mail != '' && $send_custom) {
            $either = true;
            $count3++;
            dbg('would send email to side : ' . $custom_mail);
        }
        if ($either) {
            $count4++;
        }
        $either = false;
        echo '<br>';
    }
    echo "Von insgesamt $count1 Nutzern würden für einen Beitrag aus dieser Kategorie $count4 benachrichigt werden.
    Es würden $count2 Emails an spk Emailadressen und $count3 an private Adressen verschickt werden. Durch diesen Testvorgang wurden KEINE Mails verschickt.";

}

function ip_in_range($ip, $range)
{
    if (strpos($range, '/') == false) {
        $range .= '/32';
    }
    // $range is in IP/CIDR format eg 127.0.0.1/24
    list($range, $netmask) = explode('/', $range, 2);
    $range_decimal = ip2long($range);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal = ~$wildcard_decimal;
    return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}


function give_cap_to_role($role_name, $capability_name)
{
// get the the role object
    $role_object = get_role($role_name);

// add $cap capability to this role object
    $role_object->add_cap($capability_name);

    dbg($role_object);
}


function remove_cap_from_role($role_name, $capability_name)
{
// get the the role object
    $role_object = get_role($role_name);

// remove $cap capability from this role object
    $role_object->remove_cap($capability_name);

    dbg($role_object);
}

function test_end_prize_draw($index, $change_key, $id)
{

    if (metadata_exists('post', $id, 'prize_draw_change_key') &&
        get_post_meta($id, 'prize_draw_change_key')[0] != $change_key) {
        //this is an old trigger that is invalid because the post has been edited in the past (threshold of 2 secs)
        return;
    }

    if (get_post_status($id) !== 'publish') {
        return;
    }

    $custom = get_post_custom($id);
    $ended = unserialize($custom['ended'][0]);
    $active = unserialize($custom['prize_draw_active'][0]);
    if ($ended[$index] == 'true' || $active[$index] == 'false') {
        //this prize_draw has already ended so it is not supposed to end again
        return;
    }

    $ended[$index] = 'true';
    $active[$index] = 'false';

    $at_least_one_active = false;

    foreach ($active as $i => $a) {
        if ($active[$i] == 'true' && $ended[$i] != 'true') {
            $at_least_one_active = true;
        }
        if (is_null($ended[$i])) {
            $ended[$i] = 'false';
        }
    }

    dbg($active);
    dbg($ended);

    if (!$at_least_one_active) {
        __update_post_meta($id, 'prize_draw_visible', "false");
    }
    dbg($at_least_one_active);

    $participants = json_decode($custom['prize_draw_participants'][0], true)[$index];

    $is_event = $custom['prize_draw_is_event'][0] == "true";
    $event_type = unserialize($custom['prize_draw_event_type'][0])[$index];
    $event_name = unserialize($custom['prize_draw_event_name'][0])[$index];

    $event_start_date = unserialize($custom['prize_draw_event_start_date'][0])[$index];
    $event_start_time = unserialize($custom['prize_draw_event_start_time'][0])[$index];
    if ($is_event) {
        $event_time = " am $event_start_date um $event_start_time";
    } else {
        $event_time = "";
    }

    $number_of_tickets = unserialize($custom['prize_draw_number_of_tickets'][0])[$index];
    $number_of_tickets_2 = unserialize($custom['prize_draw_amount_of_tickets'][0])[$index];

    $organizerEmail = unserialize($custom['prize_draw_email'][0])[$index];
    if (empty($organizerEmail) || !filter_var($organizerEmail, FILTER_VALIDATE_EMAIL)) {
        $organizerEmail = get_user_by('ID', get_post($id)->post_author)->user_email;
    }

    $title = get_the_title($id);
    $title = html_entity_decode($title);


    $subj = sprintf($GLOBALS['Strings']['prize_draw_email_subject'], $title);


    $number_of_participants = count($participants);
    if ($number_of_participants === 0) {

        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_no_participants_no_event'], $title, $event_type, $event_name, $event_time);

        spk_sendEmail($organizerEmail, $message, $subj);
        return;
    }

    if ($number_of_tickets >= $number_of_participants) {
        $winners = $participants;
    } else {
        $winners = array();
        for ($i = $number_of_tickets; $i > 0; $i--) {
            $winnerePos = wp_rand(0, (count($participants) - 1));
            $winners[] = $participants[$winnerePos];
            array_splice($participants, $winnerePos, 1);
        }
    }

    $winnerstring = '';
    foreach ($winners as $winner) {

        $winner_name = $winner['id'];
        //todo adress -> address
        $internal_mail = $winner['adress'];
        $winner = get_user_by("login", $winner_name);
        if ($winner) {
            $userEmail = $winner->user_email;
            $firstname = $winner->first_name;
            $surname = $winner->last_name;

            if ($is_event) {
                $message = sprintf($GLOBALS['Strings']['prize_draw_winner_email_message_event'], $firstname, $surname, $number_of_tickets_2, $event_type, $event_name, $event_time);
            } else {
                $message = sprintf($GLOBALS['Strings']['prize_draw_winner_email_message_no_event'], $firstname, $surname, $number_of_tickets_2, $event_name);
            }

            //used later in the organizer E-Mail
            $winnerstring .= "Personal-Nummer: $winner_name <br>
            Name: $firstname $surname <br>
            Email: $userEmail <br><br>";

            if ($internal_mail) {
                $winnerstring .= "Hauspost: $internal_mail <br>";
            }

            spk_sendEmail($userEmail, $message, $subj);


            $custom_mail = get_user_meta($winner->ID, 'notifications_custom_email', true);
            if ($custom_mail != '') {
                spk_sendEmail($custom_mail, $message, $subj);
            }

        } else {
            $winnerstring .= "Personal-Nummer: $winner_name <br>
        Unbekannter Nutzer <br><br>";
        }
    }

    if (sizeof($winners) > 1) {
        $winners_are = "die Gewinner sind:<br><br>";
    } else {
        $winners_are = "der Gewinner ist:<br><br>";
    }

    if ($is_event) {
        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_event'], $title, $event_type, $event_name, $event_time, $winners_are);
    } else {
        $message = sprintf($GLOBALS['Strings']['prize_draw_organizer_email_message_no_event'], $title, $event_name, $winners_are);
    }


    $message .= $winnerstring;

    $losers = $number_of_participants - sizeof($winners);
    $losers = $losers > 0 ? $losers : 0;

    $message .= "<br>Teilnehmer gesamt: $number_of_participants <br>Es haben folgende " . $losers . " Personen teilgenommen, die nicht gewonnen haben:";

    if ($losers > 0) {
        foreach ($participants as $participant) {

            $tmp_user = get_user_by("login", $participant['id']);
            if ($tmp_user) {
                $firstname = $tmp_user->first_name;
                $surname = $tmp_user->last_name;
            } else {
                $firstname = "Unbekannter Nutzer";
                $surname = "P-NR: " . $participant['id'];
            }
            $message .= "<br> - $firstname $surname (" . $participant['id'] . ")";
        }
    }
    $message .= "<br>";
    spk_sendEmail($organizerEmail, $message, $subj);
}

function get_user_list()
{
    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");
    $count = 0;
    foreach ($users as $user) {

        $user = get_user_by('ID', $user->ID);
        $count++;
        echo $count . "," . $user->first_name . ' ' . $user->last_name . ',' . $user->user_login . ',' . $user->user_email . '<br>';

    }
}

/*
dbg(
    ip_in_range('195.140.123.234','195.140.0.0/17') || ip_in_range('195.140.123.234','195.35.127.0/24')
);*/
/*
$change_key = 1652883924;
$id = 6170;
$index = 0;
if (metadata_exists('post', $id, 'prize_draw_change_key') &&
    get_post_meta($id, 'prize_draw_change_key')[0] != $change_key) {
    //this is an old trigger that is invalid because the post has been edited in the past (threshold of 2 secs)
    return;
}

if (get_post_status($id) !== 'publish') {
    return;
}

$custom = get_post_custom($id);
$ended = unserialize($custom['ended'][0]);
$active = unserialize($custom['prize_draw_active'][0]);
if ($ended[$index] == 'true' || $active[$index] == 'false') {
    //this prize_draw has already ended so it is not supposed to end again
    return;
}

echo 'end';
echo '<br>';
echo '<br>';


$is_event = $custom['prize_draw_is_event'][0];

$freikarten = '';
if ($is_event) {
    $freikarten = 'Freikarten für ';
}
$edit_prize_draw_visible = get_post_meta(6999, 'prize_draw_visible', true) =="true";

dbg($freikarten);
dbg($edit_prize_draw_visible);*/
/*

$users = get_users();

$exp = 0 ;
$nexp = 0 ;
foreach ($users as $user){
    if(Expire_Passwords::is_expired($user)){
        $exp++;
    }else{
        $nexp++;
    }
}

dbg('expired: '.$exp);
dbg('not expired: '.$nexp);*/
/*dbg($_SERVER);*/

/*
$proxy_headers = array(
    'HTTP_VIA',
    'HTTP_X_FORWARDED_FOR',
    'HTTP_FORWARDED_FOR',
    'HTTP_X_FORWARDED',
    'HTTP_FORWARDED',
    'HTTP_CLIENT_IP',
    'HTTP_FORWARDED_FOR_IP',
    'VIA',
    'X_FORWARDED_FOR',
    'FORWARDED_FOR',
    'X_FORWARDED',
    'FORWARDED',
    'CLIENT_IP',
    'FORWARDED_FOR_IP',
    'HTTP_PROXY_CONNECTION'
);
foreach($proxy_headers as $x){
    if (isset($_SERVER[$x])) die("You are using a proxy!");
}
*/

/*
$admin_obj = get_user_by('id', 245);
$admin_obj->add_cap( 'create_users');
$admin_obj->add_cap( 'edit_users');
$admin_obj->add_cap( 'delete_users');
$admin_obj->add_cap( 'list_users');
$admin_obj->add_cap( 'add_users');*/
//dbg(get_timestamp_offset());
/*$obj_existing_role=get_role("subscriber");

tododowhenpatching
//enabletheeditpublishedpostspermission
$obj_existing_role->add_cap("delete_posts");
$obj_existing_role->add_cap("delete_published_posts");
$obj_existing_role->add_cap("edit_posts");
$obj_existing_role->add_cap("edit_published_posts");
/*dbg($active);
dbg($active1);
dbg($active2);*/


/*$u = get_user_by('login','11111337');
$u->add_role( 'Developer' );*/
//freikarten neuegal q

/*require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

$files_to_zip = array(
    'filter.svg'
);

$dir = get_Server_Image_Dir();
$new_files = array();
foreach($files_to_zip as $value){
    $new_files[] = $dir.$value;
}
print_r($new_files);
// Create Object
$archive = new PclZip("compressed.zip");
/*
$compress = $archive->add($p_filelist, $p_option, $p_option_value, ...);

$files_archive = $archive->add($new_files, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, 'myFiles');
Here,
$new_files = Array of files
PCLZIP_OPT_REMOVE_PATH = http://www.phpconcept.net/pclzip/user-guide/29
PCLZIP_OPT_ADD_PATH = http://www.phpconcept.net/pclzip/user-guide/28
*/
/*$files_archive = $archive->add($new_files, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, 'myFiles');
if ($files_archive == 0) {
    die("Error : ".$archive->errorInfo(true));
}
else{
    echo "Archive Created";
    echo PCLZIP_OPT_REMOVE_PATH;
    echo "<br>";
    echo PCLZIP_OPT_ADD_PATH;
}*/

/*if ($zip->open('test_new.zip', ZipArchive::CREATE) === TRUE)
{
    // Add files to the zip file
    $zip->addFile(get_Server_Image_Dir().'filter.svg');

    // All files are added, so close the zip file.
    $zip->close();
}*/


/*
function reorder_posts( $order = array() ) {
    global $wpdb;
    $list = join(', ', $order);
    $wpdb->query( 'SELECT @i:=-1' );
    $result = $wpdb->query(
        "UPDATE wp_posts SET menu_order = ( @i:= @i+1 )
        WHERE ID IN ( $list ) ORDER BY FIELD( ID, $list );"
    );
    return $result;
}*/


/*if (in_array('business_partner', wp_get_current_user()->roles)) {
    wp_safe_redirect(home_url('/'.$post_id));
}*/


//spk_sendEmail('paulkaufmann@hotmail.de', 'hi', 'hi');
//debugg_mail('test');
//print_r(wp_roles());
//echo $current_user->ID;

/*end_prize_draw (5365,0,1637838101);
//end_prize_draw (5380,0,1637845181);
$custom = get_post_custom(5380);
$is_event = ($custom['prize_draw_is_event'][0]=='true');*/

//print_r( $custom);
//print_r( $custom);
/*$post = get_post(5389);
$subject = get_plattform_name() . ": $post->post_title";
$subject = html_entity_decode($subject);*/
//email_members(5389);
//Expire_Passwords::expire_user_password(196);
//spk_sendEmail('paulkaufmann@hotmail.de',$subject,$subject);


//update_option('sending_emails', 'false');

/*
$obj_existing_role = get_role('business_partner');
$obj_existing_role->add_cap( "read_private_pages" );
print_r($obj_existing_role);*/
/*
// enable the edit published posts permission
$obj_existing_role->add_cap( "read_private_pages" );
$obj_existing_role->add_cap( 'edit_others_posts' );
/*$obj_existing_role->add_cap( "delete_posts" );
$obj_existing_role->add_cap( "delete_published_posts" );
$obj_existing_role->add_cap( "edit_posts" );
$obj_existing_role->add_cap( "edit_published_posts" );*/
/*
$obj_existing_role = get_role('contributor');
print_r($obj_existing_role);

// enable the edit published posts permission
$obj_existing_role->add_cap( "read_private_pages" );
$obj_existing_role->add_cap( 'edit_others_posts' );*/

//echo json_encode($obj_existing_role);

/*$data = $obj_existing_role;

echo '<br>';
echo '<br>';
print_r($obj_existing_role);*/


/*
echo 'a';
$post = get_post(3821);
echo 'a';
print_r($post->post_category);
if ($post != null  && $post->post_status === 'publish' && $post->post_type === 'post') {

    print_r($post->post_category);
    if (empty(array_intersect($post->post_category, get_categories_with_notifications()))) {
        echo 'return';
    }
}

$data = get_userdata( get_current_user_id() );

if ( is_object( $data) ) {
    $current_user_caps = $data->allcaps;

    // print it to the screen
    echo '<pre>' . print_r( $current_user_caps, true ) . '</pre>';
}

/*global $wpdb;
$users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users;");


foreach ($users as $user) {

    $mail = $user->user_email;

    $notifications_enabled_for_posts = get_user_meta($user->ID, 'notifications_posts', true) != 'false';
    if ($notifications_enabled_for_posts) {
        print_r($user);
    }

    $custom_mail = get_user_meta($user->ID, 'notifications_custom_email', true);
    if ($custom_mail != '') {
        print_r($user);
    }

}*/

//add_role( 'business_partner', 'Boni Anbieter', array( 'read' => true, 'level_0' => true,'edit_published_posts'=> true,'edit_posts'=>true,'read_private_pages'=>true ) );
