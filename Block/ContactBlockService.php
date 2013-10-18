<?php
namespace Nico\ContactBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Nico\ContactBundle\Form\ContactType;
use Nico\ContactBundle\Entity\Contact;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ContactBlockService extends BaseBlockService{
	protected $container;
    protected $session;
	public function __construct($name, EngineInterface $templating,ContainerInterface $container)
    {
        parent::__construct($name, $templating);

        $this->container    = $container;
        $this->session = $this->container->get('session');
    }

    /**
	* Valid the settings data
    */
	function validateBlock(ErrorElement $errorElement, BlockInterface $block)
	{
		$errorElement
             ->with('settings.template')
                ->assertNotNull(array())
                ->assertNotBlank()
             ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(BlockInterface $block)
    {
        $this->setSettingsToEnv($block);
    }
	/**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
	    // merge settings
       $settings =  $blockContext->getSettings();

       //when you create a controller you need to bind a ContainerAware Object
       $controller = new Controller();
       $controller->setContainer($this->container);

       $contact = new Contact();
       $formName = "contactForm";
       $form = $controller->createForm($formName, $contact);

       
       $request = $this->container->get('request');

       return $this->renderResponse($blockContext->getTemplate(), array(
        'block'     => $blockContext->getBlock(),
        'settings'  => $blockContext->getSettings(),
        'form'      => $form->createView(),
        'formName'=> $formName,
        ), $response);
   }

  
    /**
    * store the variables in session for another page action
    * @param $block BlockInterface
    */
    private function setSettingsToEnv(BlockInterface $block){
       $settings =  $block->getSettings();
       $this->setNewsletterListId($settings['list']);
       $this->setDestinataires($settings['destinataires']);
       $this->setRedirection($settings['redirection']); 
    }
    /**
     * {@inheritdoc}
     * The form that will be displayed in the Admin
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('template', 'text', array()),
                array('destinataires', 'text', array('required'=>false)),
                array('list', 'text', array(
                    'extra_fields_message'=>'list list Id',
                    'label'=>'list Id',
                    'required'=>false
                    )),
                array('redirection', 'text', array('required'=>false)),
                ),
            ));
    }

    /**
     * {@inheritdoc}
     * The name of the Bloc for the Admin section
     */
    
    public function getName(){
        return 'Contact (nico)';
    }

    /**
     * {@inheritdoc}
     * Set default value for the settings
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'NicoContactBundle:Block:form.html.twig',
            'destinataires' => '',
            'list' => '',
            'redirection' => '_page_alias_contact_rdv',
            ));
    }

    protected function setNewsletterListId($listId){
        $this->newsletterListId = $listId;
        $this->session->set('mailchimp_listId',$this->newsletterListId);
    }
    protected function getNewsletterListId(){
        return $this->session->get('mailchimp_listId');
    }

    protected function setDestinataires($destinataires){
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