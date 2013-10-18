<?php

namespace Nico\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlocController extends Controller
{
    protected $container;
    protected $newsletterListId;
    protected $destinataires;
    protected $redirection;
    protected $session;
    protected $name;

    public function __construct(ContainerInterface $container,$nameService){
        $this->container = $container;
        $this->name = $nameService;
        $this->session = $this->container->get('session');
        // $this->session = new Symfony\Component\HttpFoundation\Session\Session();
    }

    public function setNewsletterListId($listId){
   		$this->newsletterListId = $listId;
        $this->session->set('mailchimp_listId',$this->newsletterListId);
    }
    public function getNewsletterListId(){
        return $this->session->get('mailchimp_listId');
    }

    public function setDestinataires($destinataires){
        $this->destinataires = $destinataires;
        $this->session->set('contact_destinataire',$this->destinataires);
    }

    public function getDestinataires(){
        return $this->session->get('contact_destinataire');
    }

    public function setRedirection($redirection){
        $this->redirection = $redirection;
        $this->session->set('contact_redirection',$this->redirection);
    }

    public function getRedirection(){
        return $this->session->get('contact_redirection');
    }

    
    
}
