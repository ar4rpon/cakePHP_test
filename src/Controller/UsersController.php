<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Utility\Security;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Tokens');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // 認証を必要としないログインアクションを構成し、
        // 無限リダイレクトループの問題を防ぎます
        $this->Authentication->addUnauthenticatedActions(['login', 'add', 'forgetPassword', 'forgetPasswordReset']);
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

    public function forgetPassword()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $user = $this->Users->findByEmail($email)->first();

            if ($user) {
                $token = Security::hash(Security::randomBytes(32), 'sha256', true);

                $existingToken = $this->Tokens->find()
                    ->where(['email' => $user->email])
                    ->first();

                if ($existingToken) {
                    $existingToken->token = $token;
                    $existingToken->token_expiration = new \DateTime('+1 day');
                    $this->Tokens->save($existingToken);
                } else {
                    $tokenData = [
                        'email' => $user->email,
                        'token' => $token,
                        'token_expiration' => new \DateTime('+1 day'),
                    ];
                    $tokenEntity = $this->Tokens->newEntity($tokenData);
                    $this->Tokens->save($tokenEntity);
                }
                $mailer = new \App\Mailer\UserMailer('gmail');
                $mailer->send('resetPassword', [$user, $token]);
                $this->Flash->success('パスワードリセットの手順をメールで送信しました');
            } else {
                $this->Flash->success('パスワードリセットの手順をメールで送信しました');
            }

            return $this->redirect(['action' => 'login']);
        }
    }

    public function forgetPasswordReset()
    {
        $token = $this->request->getData('token');

        if ($this->request->is('post')) {
            $newPassword = $this->request->getData('new_password');
            $confirmPassword = $this->request->getData('confirm_password');
            if ($newPassword !== $confirmPassword) {
                $this->Flash->error('パスワードが一致しません');
                return $this->redirect(['action' => 'login']);
            }

            $tokenData = $this->Tokens->find()
                ->where(['token' => $token])
                ->first();
            $user = $this->Users->findByEmail($tokenData->email)->first();
            if (!$tokenData) {
                $this->Flash->error('不正なアクセスです');
                return $this->redirect(['action' => 'login']);
            }

            $user = $this->Users->patchEntity($user, ['password' => $newPassword]);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your password has been updated.'));
            } else {
                $this->Flash->error(__('Unable to update your password. Please try again.'));
            }

            $token = $this->Tokens->get($tokenData->id);
            $this->Tokens->delete($token);
            return $this->redirect(['controller' => 'Todos', 'action' => 'index']);
        }
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
