<div class="users form">
    <h1>Reset Password</h1>
    <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'resetPassword']]) ?>
    <fieldset>
        <legend>Reset Your Password</legend>
        <?= $this->Form->control('current_password', [
            'type' => 'password',
            'label' => 'Current Password',
            'required' => true
        ]) ?>
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
    </fieldset>
    <?= $this->Form->button(__('Update Password')) ?>
    <?= $this->Form->end() ?>
</div>