<?php

namespace Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start_date', DateType::class, [
                'label' => 'label.start_date',
                'required' => true,
                'html5' => false, // Domyślnie ustawione na false dla większej kontroli nad wyglądem
                'format' => 'yyyy-MM-dd', // Format daty
                'attr' => [
                    'class' => 'datepicker', // Dodatkowe atrybuty HTML dla pola daty (opcjonalne)
                    'readonly' => 'readonly', // Ustaw atrybut readonly
                ],
            ])
            ->add('end_date', DateType::class, [
                'label' => 'label.end_date',
                'required' => true,
                'html5' => false, // Domyślnie ustawione na false dla większej kontroli nad wyglądem
                'format' => 'yyyy-MM-dd', // Format daty
                'attr' => [
                    'class' => 'datepicker', // Dodatkowe atrybuty HTML dla pola daty (opcjonalne)
                    'readonly' => 'readonly', // Ustaw atrybut readonly
                ],
            ]);
    }
}