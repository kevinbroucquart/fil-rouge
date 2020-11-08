<?php

namespace App\Form;

use App\Entity\Sessions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AjoutSessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', null, array('label' => false))
            ->add('description', null, array('label' => false))
            ->add('image', FileType::class, [
                'label' => false,

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '4024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ],
            ])
            ->add('adresse', null, array('label' => false))
            ->add('latitude', HiddenType::class, array('label' => false))
            ->add('longitude', HiddenType::class, array('label' => false))
            ->add('ville', HiddenType::class, array('label' => false))
            ->add('nbMin', null, array('label' => false))
            ->add('nbMax', null, array('label' => false))
            ->add('type', ChoiceType::class, 
            array(
            'choices' => array('Session entre amis' => 'Session entre amis', 'Session avec un coach' => 'Session avec un coach', 'Événement sportif' => 'Événément sportif'
            , 'Événement associatif' => 'Événement associatif'),
            'expanded' => true,
            ))
            ->add('prix', null, array('label' => false))
            ->add('dateStart', DateType::class, array(
                'years' => range(date('Y'), date('Y')),
                'label' => "Date de début"
            ))
            ->add('dateEnd', DateType::class, array(
                'years' => range(date('Y'), date('Y')),
                'label' => "Date de fin"
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sessions::class,
        ]);
    }
}
