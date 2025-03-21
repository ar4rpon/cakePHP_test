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
            <?= $this->Html->link(__('Edit Todo'), ['action' => 'edit', $todo->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Todo'), ['action' => 'delete', $todo->id], ['confirm' => __('Are you sure you want to delete # {0}?', $todo->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Todos'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Todo'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="todos view content">
            <h3><?= h($todo->todo_name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Todo Name') ?></th>
                    <td><?= h($todo->todo_name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Category') ?></th>
                    <td><?= h($todo->category) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($todo->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Status') ?></th>
                    <td><?= $todo->status ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
