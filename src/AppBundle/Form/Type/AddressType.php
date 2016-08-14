<?php
/**
 * Created by PhpStorm.
 * User: LatteCake
 * Date: 16/7/28
 * Time: 下午7:31
 * File: AddressType.php
 */

namespace AppBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;

class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ipAddress', TextType::class, [
                'label' => false,
                'required' => true
            ])
            ->add('port', TextType::class, [
                'label' => false,
                'required' => true
            ])
            ->add('auth', RadioType::class, [
                'label' => false,
                'required' => true
            ])
            ->add('password', TextType::class, [
                'label' => false,
                'required' => false
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Address'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_address';
    }

}