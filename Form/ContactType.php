<?php

namespace Nico\ContactBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', 'text',array('label'=>'Nom*'))
            ->add('prenom', 'text',array('label'=>'Prénom*'))
            ->add('civilite', 'choice', array(
                'label'=>'Civilité*',
                'choices' => array('monsieur'=>'M.',
                                   'madame' => 'Mme',
                                   'mademoiselle'=>'Mlle'
                    )
            ))
            ->add('email','email',array('label'=>'Email*'))
            ->add('adresse','textarea',array('required'=>false,'label'=>'Adresse'))
            ->add('codepostal','text',array('required'=>false,'label'=>'Code postal'))
            ->add('ville','text',array('required'=>false,'label'=>'Ville'))
            ->add('telephone','text',array('required'=>false,'label'=>'Téléphone'))
            ->add('mobile','text',array('required'=>false,'label'=>'Mobile'))
            ->add('fax','text',array('required'=>false,'label'=>'fax'))
            ->add('sujet','text',array('label'=>'Sujet'))
            ->add('message','textarea',array('label'=>'Message*'))
            ->add('newsletter','checkbox',array(
                'required'=>false,
                'label'=>'Inscription à la newsletter',

                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nico\ContactBundle\Entity\Contact'
        ));
    }

    public function getName()
    {
        return 'contactForm';
    }
}
