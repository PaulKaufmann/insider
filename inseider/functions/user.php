<?php
/**
 * Creates a user with the given parameters if these are valid and sends a welcome e-mail
 *
 * @param string $username The username of the new user
 * @param string $eMail The e-mail of the new user
 * @param string $firstname The first name of the new user
 * @param string $surname The surname of the new user
 * @param string $role The role name of the new user
 * @return bool  Whether the user was successful created or not
 */
function spk_create_User($username, $eMail, $firstname, $surname, $role)
{
    if(!is_email($eMail)){
        return false;
    }

    if (!username_exists($username) and !email_exists($eMail)) {
        $random_password = random_password_generate(10);
        $user_id = wp_create_user($username, $random_password, $eMail);
    } else {
        return false;
    }

    if (!is_wp_error($user_id) && $user_id > 0) {

        $user = get_user_by('id', $user_id);

        if ($role) {
            $user->remove_role('subscriber');
            $user->add_role($role);
        }

        $res = wp_update_user(
            array('ID' => $user_id,
                'last_name' => $surname,
                'first_name' => $firstname));

        Expire_Passwords::expire_user_password($user_id);

        if (is_wp_error($res)) {
            echo 'Fehler beim Erstellen des Nutzers. Fehler ' . $res->get_error_message();
            return false;
        } else {
            $subject =$GLOBALS['Strings']['new_use_invitation_subject'];
            $text =  sprintf($GLOBALS['Strings']['new_use_invitation_text'],$firstname,$surname,$random_password,home_url('member-login'),home_url('member-login')) ;

            spk_sendEmail($eMail, $text, $subject,false,get_invitation_attachment());
            return $user_id;
        }
    } else {
        echo 'Fehler beim Erstellen des Nutzers. Fehler ' . $user_id->get_error_message();
        return false;
    }
}

/**
 * Edits the user matching the given username with the given other parameters if these are valid
 *
 * @param string $username The username of the user that is supposed to be edited
 * @param string $eMail The new e-mail of the user
 * @param string $firstname The new first name of the user
 * @param string $surname The new surname of the user
 * @param string $role The new role name of the user
 * @return bool  Whether the user was successful created or not
 */
function spk_edit_User($username, $eMail, $firstname, $surname, $role)
{
    $user = get_user_by('login', $username);

    if (!$user) {
        return false;
    }

    $updated_values = array('ID' => $user->ID,
        'last_name' => $surname,
        'first_name' => $firstname);

    if ($eMail) {
        // check if user is really updating the value
        if ($user->user_email != $eMail) {
            // check if email is free to use
            if (!email_exists($eMail) && is_email($eMail)) {
                // Email doesn´t exist and is valid, do update value.
                $updated_values['user_email'] = $eMail;
            } else {
                // Email already exists or is not valid
                return false;
            }
        }
    }

    $update_result = !is_wp_error(wp_update_user($updated_values));

    if ($role && $update_result) {
        $user->set_role($role);
    }

    return $update_result;
}
