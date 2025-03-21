<div class="users form">
    <h1>Forget Password</h1>
    <?= $this->Form->create(null, ['url' => ['controller' => 'Users', 'action' => 'forgetPassword']]) ?>
    <fieldset>
        <legend>Forget Your Email</legend>
        <?= $this->Form->control('email', ['required' => true]) ?>

    </fieldset>
    <?= $this->Form->button(__('Reset Password')) ?>
    <?= $this->Form->end() ?>
</div>