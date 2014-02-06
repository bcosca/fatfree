<?php

class UserController extends Controller
{

    public function index()
    {
        $user = new User($this->db);

        $page = \Pagination::findCurrentPage();
        $subset = $user->paginate($page - 1, 1, array(), array('order' => 'id DESC'));
        $pages = new Pagination($subset['total'], $subset['limit']);
        $pages->setRouteKeyPrefix('page-');
        $pages->setTemplate('views/elements/pagebrowser.html');
        $pages->setRange(1);

        $this->framework->set('users', $subset);
        $this->framework->set('pagebrowser', $pages->serve());
        $this->framework->set('page_head', 'Users List');
        $this->framework->set('view', 'views/user/list.html');
    }

    public function create()
    {
        if($this->framework->exists('POST.create'))
        {
            $user = new User($this->db);
            $user->add();

            $this->framework->reroute('/');
        }
        else
        {
            $this->framework->set('page_head', 'Create User');
            $this->framework->set('view', 'views/user/create.html');
        }
    }

    public function update()
    {
        $user = new User($this->db);

        if($this->framework->exists('POST.update'))
        {
            $user->edit($this->framework->get('POST.id'));
            $this->framework->reroute('/');
        }
        else
        {
            $user->getById($this->framework->get('PARAMS.id'));
            $this->framework->set('user', $user);
            $this->framework->set('page_head', 'Update User');
            $this->framework->set('view', 'views/user/update.html');
        }
    }

    public function delete()
    {
        if($this->framework->exists('PARAMS.id'))
        {
            $user = new User($this->db);
            $user->delete($this->framework->get('PARAMS.id'));
        }

        $this->framework->reroute('/');
    }

}

?>
