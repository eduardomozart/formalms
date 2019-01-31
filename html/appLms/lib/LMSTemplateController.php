<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once _lib_ . '/TemplateController.php';
require_once _lms_ . '/lib/LMSTemplateModel.php';

final class LMSTemplateController extends TemplateController {

    private $model;

    protected function __construct() {
        
        $this->model = new LMSTemplateModel();

        $this->setLayout($this->model->selectLayout());
        $this->templateFolder = _folder_lms_;

        parent::__construct();
    }

    public function show() {

        $this->showLogo();
        $this->showMenu();
        $this->showCart();
        $this->showProfile();
        $this->showHelpDesk(); // Temporary solution before helpdesk refactoring.
        
        parent::show();
    }

    private function showLogo() {
        
        $this->render('logo', 'logo', array(
            'user'          => $this->model->getUser()
          , 'logo'          => $this->model->getLogo()
          , 'currentPage'   => $this->model->getCurrentPage()
          , 'homePage'      => $this->model->getHomePage()
        ));
    }

    private function showMenu() {
        
        $this->render('menu', 'main-menu', array(
            'user'          => $this->model->getUser()
          , 'menu'          => $this->model->getMenu()
          , 'currentPage'   => $this->model->getCurrentPage()
        ));
    }

    private function showCart() {
        
        $this->render('cart', 'cart', array(
            'user'          => $this->model->getUser()
          , 'cart'          => $this->model->getCart()
          , 'currentPage'   => $this->model->getCurrentPage()
        ));
    }

    private function showProfile() {
        
        $this->render('profile', 'profile', array(
            'user'              => $this->model->getUser()
          , 'profile'           => $this->model->getProfile()
          , 'credits'           => $this->model->getCredits()
          , 'career'            => $this->model->getCareer()
          , 'subscribeCourse'   => $this->model->getSubscribeCourse()
          , 'news'              => $this->model->getNews()
          , 'languages'         => $this->model->getLanguages()
          , 'currentPage'       => $this->model->getCurrentPage()
        ));
    }

    private function showHelpDesk() {

        // Temporary solution before helpdesk refactoring.        
        $this->render('helpdesk_modal', 'helpdesk', array(
            'user'          => $this->model->getUser()
          , 'userDetails'   => $this->model->getUserDetails()
          , 'email'         => $this->model->getHelpDeskEmail()
          , 'currentPage'   => $this->model->getCurrentPage()
        ));
    }
}
