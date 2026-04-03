<?php

/*
 * 2.0
 * new strings:
 * - admin_idea_subject
 * - admin_sick_note_message
 * - User_Management_login_label
 * */

$GLOBALS['Strings'] = array(
    //no params
    'share-message' => "Hier ein interessanter Beitrag im " .
        get_plattform_name() . ": %0D%0A",

    //$current_user->user_login,$current_user->first_name, $current_user->last_name, get_the_permalink(), get_the_title());
    'request_activation_email_message' => '<p>Hallo,<br>
                    der Nutzer %s (%s %s) hat im ' . get_plattform_name() . ' um die Freigabe folgendes Beitrags gebeten:<br>
                    <a href="%s">%s</a></p>',

    'request_activation_email_subject' => 'Die Freigabe eines Beitrags wurde beantragt',


    'new_use_invitation_subject' =>'Willkommen im ' . get_plattform_name(),

    //$firstname,$surname,$random_password,home_url('member-login'),home_url('member-login')
    'new_use_invitation_text' =>'Hallo %s %s, <br>
                für Sie wurde ein Account im ' . get_plattform_name() . ' angelegt! 
                <br><br>Ihr Passwort lautet: <strong>%s</strong><br><br>
                Sie können sich mit Ihrer Sparkassen-E-Mail-Adresse oder Ihrer Personalnummer anmelden unter:<br><a href="%s">%s</a><br>',


    //get_team_Email()
    'spk_sendmail_signature' => '<br>Dies ist eine automatisch generierte E-Mail. Bitte antworten Sie nicht darauf.<br>
                    Bei Fragen rund um den ' . get_plattform_name() . ' wenden Sie sich bitte an <a href="mailto:%1$s">%1$s</a>.<br>
                    <br> 
                    Freundliche Grüße<br>
                    Ihr ' . get_plattform_name() . '-Team',

    //$post->post_title
    'new_post_subject' => get_plattform_name() . ': %s',

    //$link,$title,$excerpt
    'new_post_message_spk_mail' => 'Hallo,<br>es gibt einen neuen Beitrag im ' . get_plattform_name() . ', der Sie interessieren könnte:<br>
          <a href="%s">%s</a>
          <br>%s
          <br><br><a href="%1$s">%1$s</a><br>',

    //no params
    'new_post_censored_subject' => 'Etwas Neues im ' . get_plattform_name(),

    //$link
    'new_post_censored_message_spk_mail' => 'Hallo,<br>es gibt einen neuen Beitrag im ' . get_plattform_name() . ', der Sie interessieren könnte:<br>
          <br><br><a href="%1$s">%1$s</a><br>',

    //no params
    'comment_admin_subject' => 'Neue Kommentare im ' . get_plattform_name(),

    //$receiver->first_name, $receiver->last_name
    'comment_admin_message' => '<p>Hallo %s %s,<br>
                    es gibt neue Kommentare im ' . get_plattform_name() . '. Folgende Beiträge wurden in den letzten 24 Stunden diskutiert:</p>',

    //no params
    'comment_user_subject' => 'Neue Kommentare zu Ihrem Beitrag im ' . get_plattform_name(),

    //$receiver->first_name, $receiver->last_name
    'comment_user_message' => '<p>Hallo %s %s,<br>
                    folgende Ihrer Beiträge wurden in den letzten 24 Stunden im ' . get_plattform_name() . ' diskutiert:</p>',

    //region Prize draws

    // Die strings hier sind teilweise etwas komisch, da sie für Gewinnspiele mit oder ohne Termine arbeiten müssen.
    // Fehlende Leerzeichen sind so gewollt und verhindern, dass bei Gewinnspielen ohne Termine doppelte Leerzeichen entstehen, da manche Parameter dann leere Strings enthalten

    //$title
    'prize_draw_email_subject' => get_plattform_name() . ' %s',

    //$title,$event_type,$event_name,$event_time
    'prize_draw_organizer_email_message_no_participants' => 'Hallo,<br>Ihr Gewinnspiel %s ist ausgelaufen, leider hat niemand teilgenommen.<br>',

    //$title,$event_type,$event_name,$event_time, $winners_are ( = "die Gewinner sind" / "der Gewinner ist:")
    'prize_draw_organizer_email_message_event' => 'Hallo,<br>Ihr Gewinnspiel "%s" für Freikarten für %s %s%s ist ausgelaufen und %s',

    //$title, $event_name, $winners_are ( = "die Gewinner sind" / "der Gewinner ist:")
    'prize_draw_organizer_email_message_no_event' => 'Hallo,<br>Ihr Gewinnspiel "%s" ist ausgelaufen und %3$s',

    //$firstname, $surname, $number_of_tickets_2, $event_type, $event_name, $event_time
    'prize_draw_winner_email_message_event' => 'Hallo %s %s,<br>Sie haben bei einem Gewinnspiel im ' . get_plattform_name() . ' %s Freikarten für %s %s%s gewonnen! <br>',

    //$firstname, $surname, $number_of_tickets_2, $event_name
    'prize_draw_winner_email_message_no_event' => 'Hallo %s %s,<br>Sie haben bei einem Gewinnspiel im ' . get_plattform_name() . ' %s %s gewonnen! <br>',


    'upload_video_message_mobile' => 'Maximale Dateigröße 1Gb, Empfohlene Größe: 1920px x 1080px (Querformat, 16:9)',


    'client_sick_note_subject' => 'Ihre Nachricht an die Personalabteilung',

    //$fullname, $remark
    'client_sick_note_message' => 'Hallo %s,<br>
                                vielen Dank, wir haben Ihre Krankmeldung erhalten.
                                Bitte antworten Sie nicht auf diese E-Mail, sondern wenden Sie sich für weitere Informationen zu Ihrer Krankheit an Ihre Führungskraft.<br>
                                Gute Besserung und viele Grüße<br>
                                Ihre Personalabteilung<br>',
    //$fullname, get_domain(), $remark
    'admin_sick_note_message' => 'Sehr geehrte Damen und Herren,<br>
                           Der/Die Mitarbeiter/in <strong>%s</strong> hat sich über %s krankgemeldet.<br>
                           Anbei finden Sie die hochgeladene Arbeitsunfähigkeitsbescheinigung.<br><br>%s',

    'emptyTextSickNote' => 'Bitte klicken Sie auf diesen Bereich, um ein Foto der Arbeitsunfähigkeitsbescheinigung hochzuladen. <br>1 Bild, maximal %s MB',

    'idea_label' =>'Hinweise',

    'client_idea_subject' => 'Ihr Hinweis wurde erfasst',

    //$fullname, $remark, $idea_nr
    'client_idea_message' => 'Hallo %s,<br>
                            vielen Dank, wir haben Ihren Hinweis erhalten.<br>
                            Bitte antworten Sie nicht auf diese E-Mail, sondern wenden Sie sich für weitere Informationen zu Ihrem Hinweis direkt an die Abteilung Zentrale Dienste.<br>
                            Viele Grüße<br>
                            Ihre Abteilung Zentrale Dienste<br>',


    //$fullname, $remark, $idea_nr
    'admin_idea_message' => 'Sehr geehrte Damen und Herren,<br>
                        Der/Die Mitarbeiter/in %s hat über den ' . get_plattform_name() . ' folgende Anregung (Nr.: %3$s) eingereicht:<br><br>%2$s</p><br>
                        Im Anhang finden Sie eventuelle Bilder, die %1$s hochgeladen hat.<br>',

    //$fullname, $idea_nr
    'admin_idea_subject' => 'Hinweis von %s, Nr %s',

    'sick_note_terms' => 'Bitte beachten Sie, dass dieser Weg der Übermittlung eine für Sie freiwillige und optionale
                    Übermittlungsmöglichkeit für Arbeitsunfähigkeitsbescheinigungen (AU) darstellt. Die Anforderungen an
                    Vertraulichkeit und Sicherheit der Übermittlung entsprechen der Qualität einer normalen privaten E-Mail.',

    'idea_terms' => 'Bitte beachten Sie, dass auf diesem Weg keine vertraulichen Daten weitergegeben werden dürfen.
                    Falls auf dem/den von Ihnen eingereichten Foto/s eine/mehrere Person/en abgebildet ist/sind,
                    bestätigen Sie mit dem Hochladen, dass diese Person/en mit der Verwendung/Weiterverarbeitung des/r
                    Fotos einverstanden ist/sind.',
    //size
    'emptyTextIdea' => 'Bitte klicken Sie auf diesen Bereich, um optionale Fotos zur Veranschaulichung anzufügen.<br>Maximal 3 Bilder mit insgesamt maximal %s MB',

    'communications_thank_you' => 'Vielen Dank für Ihre Nachricht!',
    'emptyTextVideo' => 'Bitte legen Sie das Beitragsvideo hier ab oder klicken Sie auf diesen Bereich. <br> Maximale Dateigröße 1Gb, Empfohlene Größe: 1920px x 1080px (Querformat, 16:9)',
    'emptyTextAudio' => 'Bitte legen Sie die Audiodatei hier ab oder klicken Sie auf diesen Bereich.',
    'emptyTextGallery' => 'Bitte legen Sie die Galeriebilder hier ab oder klicken Sie auf diesen Bereich. <br> Empfohlene Größe: 1920px x 1080px (Querformat, 16:9)',
    'emptyTextThumbnail' => 'Bitte legen Sie das Vorschaubild hier ab oder klicken Sie auf diesen Bereich.<br> Empfohlene Größe: 1920px x 1080px (Querformat, 16:9)',
    'emptyTextDocument' => 'Bitte legen Sie den Anhang hier ab oder klicken Sie auf diesen Bereich.',
    'User_Management_login_label' => 'Name / Personal-Nr.',
    'User_Management_username_label' => 'Personal-Nr.'


//endregion
);

/*
 *
 sprintf($GLOBALS['Strings'][''])
 $GLOBALS['Strings']['']
 * */