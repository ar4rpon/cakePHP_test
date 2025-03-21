<div class="users form">
    <h1>Reset Password</h1>
    <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'forgetPasswordReset']]) ?>
    <fieldset>
        <legend>Reset Your Password</legend>
        <?= $this->Form->control('new_password', [
            'type' => 'password',
            'label' => 'New Password',
            'required' => true
        ]) ?>
        <?= $this->Form->control('confirm_password', [
            'type' => 'password',
            'label' => 'Confirm New Password',
            'required' => true
        ]) ?>
        <?= $this->Form->hidden('token', ['value' => $this->getRequest()->getQuery('token')]) ?>
    </fieldset>
    <?= $this->Form->button(__('Reset Password')) ?>
    <?= $this->Form->end() ?>
</div>