<?php


class AccountController extends Controller
{
    protected $auth_actions = array('index', 'signout', 'follow');

    public function indexAction(){
        $user = $this->session->get('user');
        $followings = $this->db_manager->get('Following')->fetchAllFollowingByUserId($user['id']);
        return $this->render(array('user' => $user, 'followings' => $followings));
    }

    public function signinAction(){
        if ($this->session->isAuthenticated()){
            return $this->render('/account');
        }
        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signin'),
        ));
    }

    public function authenticateAction(){
        if ($this->session->isAuthenticated()){
            return $this->redirect('/account');
        }
        if (!$this->request->isPost()){
            $this->forward404();
        }

        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signin', $token)){
            return $this->redirect('/account/signin');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');
        $errors = array();

        if (!strlen($user_name)){
            $errors[] = 'ユーザIDを入力してください';
        }
        if (!strlen($password)){
            $errors[] = 'パスワードを入力してください';
        }

        if (count($errors) === 0){
            $user_obj = $this->db_manager->get('User');
            $user = $user_obj->fetchbyUserName($user_name);

            if (!$user || ($user['password'] !== $user_obj->hashPassword($password))){
                $errors[] = 'ユーザIDかパスワードが不正です';
            } else {
                $this->session->setAuthenticated(true);
                $this->session->set('user', $user);
                return $this->redirect('/');
            }
        }
        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            '_token'    => $this->generateCsrfToken('account/signin'),
        ), 'account/signin');
    }

    public function signupAction(){
        return $this->render(array(
            'user_name' => '',
            'password'  => '',
            '_token'    => $this->generateCsrfToken('account/signup'),
        ));
    }

    public function registerAction(){
        if (!$this->request->isPost()){
            $this->forward404();
        }
        $token = $this->request->getPost('_token');
        if (!$this->checkCsrfToken('account/signup', $token)){
            return $this->redirect('/account/signup');
        }

        $user_name = $this->request->getPost('user_name');
        $password = $this->request->getPost('password');
        $errors  = array();

        if (!strlen($user_name)){
            $errors[] = 'ユーザIDを入力してください';
        } else if (!preg_match('/^\w{3,20}$/', $user_name)){
            $errors[] = 'ユーザIDは半角英数字及びアンダースコアを3〜20文字以内で入力してください';
        } else if (!$this->db_manager->get('User')->isUniqueUserName($user_name)){
            $errors[] = 'ユーザIDはすでに使われています';
        }

        $pass_len = strlen($password);
        if (!$pass_len){
            $errors[] = 'パスワードを入力して下さい';
        } else if (4 > $pass_len || $pass_len > 30){
            $errors[] = 'パスワードは4〜30文字以内で入力してください。';
        }

        if (count($errors) === 0){
            $user_obj = $this->db_manager->get('User');
            $user_obj->insert($user_name, $password);
            $this->session->setAuthenticated(true);
            $user = $user_obj->fetchByUserName($user_name);
            $this->session->set('user', $user);

            return $this->redirect('/');
        }
        return $this->render(array(
            'user_name' => $user_name,
            'password'  => $password,
            'errors'    => $errors,
            '_token'    => $this->generateCsrfToken('account/signup'),
        ), 'signup');
    }

    public function signoutAction(){
        $this->session->clear();
        $this->session->setAuthenticated(false);
        return $this->redirect('/account/signin');
    }

    public function followAction(){
        if (!$this->request->isPost()){
            $this->forward404();
        }
        $following_name = $this->request->getPost('following_name');
        if (!$following_name){
            $this->forward404();
        }
        $token = $this->request->getPost('_token');
        if ($this->checkCsrfToken('account/follow', $token)){
            return $this->redirect('/user/' . $following_name);
        }

        $follow_user = $this->db_manager->get('User')->fetchByUserName($following_name);
        if (!$follow_user){
            $this->forward404();
        }
        $user = $this->session->get('user');
        $following_obj = $this->db_manager->get('Following');
        if ($user['id'] !== $follow_user['id'] && !$following_obj->isFollowing($user['id'], $follow_user['id'])){
            $following_obj->insert($user['id'], $follow_user['id']);
        }
        return $this->redirect('/account');
    }
}