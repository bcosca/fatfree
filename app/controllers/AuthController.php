<?php

class AuthController extends Controller
{

    public function login()
    {
        if($this->framework->exists('POST.login'))
        {
            $userdetails = $this->db->exec("SELECT * FROM users WHERE username = :username AND password = :password", array(
                ':username' => $this->framework->clean($this->framework->get('POST.username')),
                ':password' => md5($this->framework->get('POST.password')),
            ));

            if($this->db->count() > 0)
            {
                $this->framework->set('SESSION.user', $userdetails[0]);
                $this->framework->reroute('/words');
            }
            else
            {
                $this->framework->push('SESSION.messages', array(
                    'text' => 'Invalid username or password',
                    'type' => 'error'
                ));
                $this->framework->reroute('/');
            }
        }

        $this->framework->set('view', 'views/login.html');
    }

    public function logout()
    {
        $this->framework->clear('SESSION');
        $this->framework->reroute('/');
    }

}

?>
