<?php

namespace App\Mailer;

use Cake\Mailer\Mailer;

class UserMailer extends Mailer
{
    public function resetPassword($user, $token)
    {
        $resetUrl = 'http://localhost:8080/users/forget_password_reset?token=' . $token;

        return $this
            ->setTo($user->email)
            ->setSubject('パスワードリセットのご案内')
            ->setViewVars([
                'user' => $user,
                'resetUrl' => $resetUrl
            ])
            ->setEmailFormat('both')
            ->viewBuilder()
            ->setTemplate('reset_password');
    }
}
