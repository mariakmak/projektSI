<?php
/**
 * Transaction type.
 */

namespace App\Form\Type;

use App\Entity\Transaction;
use App\Entity\Categories;
use App\Entity\Currency;
use App\Entity\Wallet;
use App\Entity\User;
use App\Repository\CategoriesRepository;
use App\Repository\WalletRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Repository\TransactionRepository;
use App\Config\TextAlign;
use Symfony\Component\Security\Core\Security;


/**
 * Class TransactionType.
 */
class TransactionType extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }



    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => Categories::class,
                'choice_label' => function ($category): string {
                    return $category ->getName() ;
                },
                'label' => 'label.category',
                'placeholder' => 'label.none',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'query_builder' => function (CategoriesRepository $tg) {
                    return $tg->queryByAuthor($this->security->getUser());
                },
            ]
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'label.name',
                'required' => true,
                'attr' => ['max_length' => 64],
            ]);



        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.description',
                'required' => false,
                'attr' => ['max_length' =>250],
            ]);





        $builder->add(
            'sum',
            NumberType::class,
            [
                'label' => 'label.sum',
                'required' => true,

            ]);

        $builder->add(
            'value',
            CheckboxType::class,[
            'label' => 'label.income',
            'required' => false

            ]);





        $builder->add(
            'currency',
            EntityType::class,
            [
                'class' => Currency::class,
                'choice_label' => function ($currency): string {
                    return $currency->getName();
                },
                'label' => 'label.currency',
                'placeholder' => 'label.none',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ]
        );
            //CurrencyType::class,
           // [
              //  'label' => 'label.currency',

            //]);


        $builder->add(
            'wallet',
            EntityType::class,
            [
                'class' => Wallet::class,
                'choice_label' => function ($wallet): string {
                    return $wallet->getName();
                },
                'label' => 'label.wallet',
                'placeholder' => 'label.none',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
                'query_builder' => function (WalletRepository $tg) {
                    return $tg->queryByAuthor($this->security->getUser());
                },
            ]
        );
    }














    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Transaction::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'transaction';
    }
}