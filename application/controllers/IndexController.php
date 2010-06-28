<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {

    }

    public function contactsAction()
    {
        // action body

        $self = 'http://' . $_SERVER['SERVER_NAME'] . $this->view->url(array('controller'=>'index','action'=>'contacts'));
        //Zend_Debug::dump($this->getRequest());
        $token = $this->getRequest()->getParam('token');
        if (isset($token)) {
            Zend_Registry::set('contact_token', Zend_Gdata_AuthSub::getAuthSubSessionToken($token));
            header('Location: ' . $self);
            exit;
        }

        if (!isset(Zend_Registry::get('contact_token'))) {
            $scope = 'http://www.google.com/m8/feeds';
            $uri = Zend_Gdata_AuthSub::getAuthSubTokenUri($self, $scope, 0, 1);
            header('Location: ' . $uri);
            exit;
        }

        $client = Zend_Gdata_AuthSub::getHttpClient(Zend_Registry::get('contact_token'));
        $gdata = new Zend_Gdata($client);
        $query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full');
        $feed = $gdata->getFeed($query);
        $entries = $gdata->retrieveAllEntriesForFeed($feed);

        $contacts = array();


        foreach ($entries as $entry) {
            $name = $entry->title->text;
            $email = null;                                                                                                                                           
            $extensions = $entry->getExtensionElements();
            foreach ($extensions as $extension) {
                if ($extension->rootElement == 'email') {
                    $attributes = $extension->getExtensionAttributes();
                    $email = $attributes['address']['value'];
                }
            }
            if ($email) {
                $contacts[] = array('name' => $name, 'email' => $email);
            }
        }

        foreach ($contacts['email'] as $email) {

            echo $email.",";
        }
    }
}

