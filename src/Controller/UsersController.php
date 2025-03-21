<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // 認証を必要としないログインアクションを構成し、
        // 無限リダイレクトループの問題を防ぎます
        $this->Authentication->addUnauthenticatedActions(['login', 'add']);
    }

    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        // POST, GET を問わず、ユーザーがログインしている場合はリダイレクトします
        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Todos',
                'action' => 'index',
            ]);

            return $this->redirect($redirect);
        }
        // ユーザーが submit 後、認証失敗した場合は、エラーを表示します
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    public function resetPassword()
    {
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('You need to be logged in to reset your password.'));
            return $this->redirect(['action' => 'login']);
        }

        $user = $this->Users->get($user->id);

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!empty($data['current_password']) && !empty($data['new_password']) && !empty($data['confirm_password'])) {
                // 新しいパスワードと確認用パスワードが一致するか確認
                if ($data['new_password'] !== $data['confirm_password']) {
                    $this->Flash->error(__('New password and confirmation password do not match.'));
                    return;
                }

                // パスワードハッシャーを使用
                $hasher = new \Authentication\PasswordHasher\DefaultPasswordHasher();

                if ($hasher->check($data['current_password'], $user->password)) {
                    $user = $this->Users->patchEntity($user, ['password' => $data['new_password']]);
                    if ($this->Users->save($user)) {
                        $this->Flash->success(__('Your password has been updated.'));
                        return $this->redirect(['controller' => 'Todos', 'action' => 'index']);
                    } else {
                        $this->Flash->error(__('Unable to update your password. Please try again.'));
                    }
                } else {
                    $this->Flash->error(__('The current password is incorrect.'));
                }
            } else {
                $this->Flash->error(__('Please fill in all required fields.'));
            }
        }

        $this->set(compact('user'));
    }

    public function logout()
    {
        $result = $this->Authentication->getResult();
        // POST, GET を問わず、ユーザーがログインしている場合はリダイレクトします
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
