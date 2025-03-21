<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Todo $todo
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Todos'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="todos form content">
            <?= $this->Form->create($todo) ?>
            <fieldset>
                <legend><?= __('Add Todo') ?></legend>
                <?php
                    echo $this->Form->control('todo_name');
                    echo $this->Form->control('category');
                    echo $this->Form->control('status');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
