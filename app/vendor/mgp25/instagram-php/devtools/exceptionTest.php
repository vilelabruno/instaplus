<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

use InstagramAPI\Exception\ServerMessageThrower;

/*
 * Emulates various server message strings and verifies that they're mapped
 * correctly by the ServerMessageThrower's parser.
 */
$exceptionsToTest = [
    'InstagramAPI\\Exception\\LoginRequiredException'      => ['login_required'],
    'InstagramAPI\\Exception\\FeedbackRequiredException'   => ['feedback_required'],
    'InstagramAPI\\Exception\\CheckpointRequiredException' => ['Abra o Instagram pelo aplicativo e clique em "Fui eu" e desabilite a autenticação de 2 fatores.'],
    'InstagramAPI\\Exception\\IncorrectPasswordException'  => ['A senha que você digitou está incorreta. Por favor, tente novamente.'],
    'InstagramAPI\\Exception\\AccountDisabledException'    => ['Sua conta foi desativada por violar as politicas do Instagram, entre em contato com eles para obter ajuda.'],
];

foreach ($exceptionsToTest as $exceptionClassName => $testMessages) {
    foreach ($testMessages as $testMessage) {
        try {
            ServerMessageThrower::autoThrow(null, $testMessage);
        } catch (\InstagramAPI\Exception\InstagramException $e) {
            $thisClassName = get_class($e);
            if ($exceptionClassName == $thisClassName) {
                echo "{$exceptionClassName}: OK!\n";
            } else {
                echo "{$exceptionClassName}: Got {$thisClassName} instead!\n";
            }
        }
    }
}
