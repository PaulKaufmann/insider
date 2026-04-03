<?php

add_action('hook_for_breach_testing', 'test_caching_breach', 10);

function test_caching_breach()
{
    $entityBody = file_get_contents('https://inseider.info');

    if (!strpos($entityBody, 'loginform')) {
        spk_sendEmail('kaufmann@muximum.de', 'SECURITY BREACH FOUND', 'SECURITY BREACH FOUND', 'Paulkaufmann@hotmail.de');
    }
}