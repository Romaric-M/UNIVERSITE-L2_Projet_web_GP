<?php

namespace App\Form;

use App\Entity\Annonce;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/* PAREIL QUE CREATEANNONCETYPE.PHP, UTILISE POUR QUOI ET OU */
class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('date', DateType::class)
            ->add('description')
            ->add('user',EntityType::class,['class' => User::class, 'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('u');



            }, 'choice_label' => 'email',"label"=>"Quel utilisateur ?"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
