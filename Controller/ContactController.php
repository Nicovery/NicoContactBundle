<?php

namespace Nico\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nico\ContactBundle\Entity\Contact;
use Nico\ContactBundle\Form\ContactType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Nico\MailchimpBundle\Event\SaveFormEvent;
use Nico\MailchimpBundle\Event\MailchimpEvents;
class ContactController extends Controller
{
    protected $msgEnvoieSuccess = 'Votre message a bien été envoyé.';
    protected $msgChampsInvalide = 'Champs invalides.';
    protected $defaultEntity ='NicoContactBundle:Contact';
    protected $aliasHomepage = '_page_alias_homepage';
    
    /**
    * 
    * @param $request Request
    */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(new ContactType, $contact);
        if($this->formIndex('homepage',$request)){
            return $this->redirect($this->generateUrl('homepage'));
        }
        return $this->render('NicoContactBundle:Default:index.html.twig',array('form'=>$form->createView()));
    }

    
    /**
    * Save the form send by a contactBlockService
    * @param $request Request 
    */
    public function saveAction(Request $request){
        if($request->getMethod()== 'POST'){
            $contact = new Contact;
            $formType = new ContactType;

            $form = $this->createForm($formType, $contact);

            $form->handleRequest($request);
            $formArray = $request->request->all();
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($contact);
                $em->flush();

                //sauvegarde mailchimp si besoin
                $newsletter = $form->getData()->getNewsletter();
                $contactBlock = $this->container->get('nico.contact.block.contact');
                if(!empty($newsletter)){
                    $dataMailchimp = $this->dataToMailChimp($form->getData());

                    $key = $this->get('nico.mailchimp.config')->getKey();
                    $listId = $contactBlock->getNewsletterListId();
                    $event = new SaveFormEvent($dataMailchimp,$listId,$key);
                    $this->get('event_dispatcher')->dispatch(MailchimpEvents::onSaveToMailchimp,$event);
                }
                    $this->sendEmail($contactBlock->getDestinataires(),$form->getData());
                $this->get('nico.error.message')->success($this->msgEnvoieSuccess);
            } else {
                $this->get('nico.error.message')->error($this->msgChampsInvalide);
                $this->get('nico.error.message')->error($form->getErrors());
            }

            return $this->redirect($this->generateUrl($this->getRedirection()));
        }
        return $this->redirect($this->generateUrl($this->aliasHomepage));
    }

    /**
    *   @param $emails string email(s) to send the data of the forms
    *   @param $data data to send in the email
    */
    private function sendEmail($emails,$data){
        if(!empty($emails)){
            $message = \Swift_Message::newInstance()
            ->setSubject('Formulaire contact site internet:')
            ->setFrom('contact@securite-auto-moto.com')
            ->setTo($emails)
            ->setContentType('text/html')
            ->setBody(
                $this->renderView(
                    'NicoContactBundle:Email:contact.txt.twig',
                    array('data' => $data)
                    )
                )
            ;
            $this->get('mailer')->send($message);
            
        }
    }

        //get the redirection alias
    private function getRedirection()
    {
        //get the redirection set in the bloc settings
        $contactBlock = $this->get('nico.contact.block.contact');
        $redirection = $contactBlock->getRedirection();
        if(empty($redirection)){
            return $this->aliasHomepage;
        }else{
            return $contactBlock->getRedirection();

        }
    }

    /**
    *   link the entity data to the mailchimp form
    *   @param $entity entity from form  
    */
    public function dataToMailChimp($entity){
        return array(
            'LNAME'=>$entity->getNom(),
            'FNAME'=>$entity->getPrenom(),
            'EMAIL'=>$entity->getEmail(),
            );
    }
}
