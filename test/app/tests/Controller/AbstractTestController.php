<?php
/**
 * Abstract Test Controller.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Entity\Currency;
use App\Repository\UserRepository;
use App\Repository\CurrencyRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract class for controller tests.
 */
abstract class AbstractTestController extends WebTestCase
{
    /**
     * Test client.
     */
    protected KernelBrowser $httpClient;

    /**
     * Translator.
     */
    protected TranslatorInterface $translator;

    /**
     * Set up tests.
     */
    public function setUp(): void
    {
        $this->httpClient = static::createClient();
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    /**
     * Provides user roles and expected outcomes.
     */
    public function roleProvider(): array
    {
        return [
            'unauthenticated user' => [null, 302, '/login'],
            'regular user' => [[UserRole::ROLE_USER->value], 200, null],
            'admin user' => [[UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 200, null],
        ];
    }

    /**
     * Create user.
     *
     * @param array $roles User roles
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    protected function createUser(array $roles): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    /**
     * Create user with specific email. It allows to create many unique users.
     *
     * @param array $roles User roles
     * @param string $email User email
     *
     * @return User User entity
     *
     * @throws ContainerExceptionInterface|NotFoundExceptionInterface|ORMException|OptimisticLockException
     */
    protected function createUserWithEmail(array $roles, string $email): User
    {
        $passwordHasher = static::getContainer()->get('security.password_hasher');
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                'p@55w0rd'
            )
        );
        $userRepository = static::getContainer()->get(UserRepository::class);
        $userRepository->save($user);

        return $user;
    }

    /**
     * Assert back to list functionality.
     */
    public function assertBackToList(): void
    {
        $crawler = $this->httpClient->getCrawler();
        $currentUrl = $crawler->getUri();

        $linkLabel = $this->translator->trans('action.back_to_list');
        #echo "\nLink label: ".$linkLabel."\n";

        $linkCrawler = $crawler->selectLink($linkLabel);
        #echo "[DEBUG] selectLink count: " . $linkCrawler->count() . "\n";

        if ($linkCrawler->count() === 0) {
            #echo "[DEBUG] Link with label '{$linkLabel}' NOT FOUND!\n";
            #echo "[DEBUG] Page HTML:\n" . $crawler->filter('body')->html() . "\n";
            $this->fail("Link with label '{$linkLabel}' was not found on the page.");
        }


        $this->assertNotEmpty($linkCrawler->link()->getUri());

        $this->httpClient->click($linkCrawler->link());
        $this->assertResponseStatusCodeSame(200);

        $this->httpClient->request('GET', $currentUrl);
        $this->assertResponseIsSuccessful();
    }

    /**
     * Assert navbar functionality.
     */
    public function assertNavbar(): void
    {
        $crawler = $this->httpClient->getCrawler();

        $navCrawler = $crawler->filter('nav.navbar');
        $this->assertGreaterThan(
            0,
            $navCrawler->count(),
            'Navbar element not found on the page.'
        );

        $links = $navCrawler->filter('a');
        $this->assertGreaterThan(
            0,
            $links->count(),
            'No links found inside the navbar.'
        );

        #echo "[DEBUG] Found " . $links->count() . " links in navbar.\n";

        $startUrl = $this->httpClient->getRequest()->getUri();

        foreach ($links as $linkElement) {
            $href = $linkElement->getAttribute('href');
            #$text = trim($linkElement->textContent);

            #echo "[DEBUG] Checking link: {$text} ({$href})\n";

            if (preg_match('#/logout$|^/$#', $href)) {
                $this->assertNotEmpty($href, 'Logout link has empty href.');
                #echo "[DEBUG] Skipping logout link: {$href}\n";
                continue;
            }

            $this->httpClient->request('GET', $href);
            $this->assertResponseIsSuccessful();

            $this->httpClient->request('GET', $startUrl);
        }
    }

    /**
     * Assert dropdown menu in a table row.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $row Table row crawler
     * @param int $expectedCount Oczekiwana liczba linków w dropdown menu (domyślnie 3)
     */
    protected function assertDropdownMenu(Crawler $row, int $expectedCount = 3): void
    {
        // dropdown menu
        $tds = $row->filter('td');
        Assert::assertGreaterThan(0, $tds->eq($tds->count() - 1)->filter('.dropdown')->count(), 'Dropdown menu not found');
        Assert::assertCount($expectedCount, $tds->eq($tds->count() - 1)->filter('.dropdown-menu li a'));

        // Linki w menu
        $id = $tds->eq(0)->text();
        $tds->eq($tds->count() - 1)->filter('a')->each(function ($link) use ($id) {
            Assert::assertStringContainsString((string)$id, $link->attr('href'));
        });
    }

    /**
     * Assert presence and correctness of a 'create' link in the view.
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @param string $routeName Symfony route name (e.g. 'category_create')
     * @param string|null $label Optional label to search for (default: translated 'action.create')
     */
    protected function assertCreateLink(Crawler $crawler, string $routeName, ?string $label = null): void
    {
        $label = $label ?? $this->translator->trans('action.create');
        $link = $crawler->selectLink($label);
        Assert::assertGreaterThan(0, $link->count(), "Create link with label '{$label}' not found.");
        $href = $link->link()->getUri();
        Assert::assertStringContainsString($routeName, $href, "Create link does not contain expected route '{$routeName}'.");
    }

    /**
     * Create wallet for user.
     *
     * @param User $user User entity
     *
     * @return \App\Entity\Wallet
     */
    protected function createWalletForUser(User $user): \App\Entity\Wallet
    {
        #$names = ['USD', 'EUR', 'GBP', 'JPY', 'PLN'];
        $currency = new \App\Entity\Currency();
        #$currency->setName($names[array_rand($names)]);
        $currency->setName('USD');
        $currencyRepository = static::getContainer()->get(\App\Repository\CurrencyRepository::class);
        $currencyRepository->add($currency, true);
        // DEBUG: wypisz ID i nazwę waluty po zapisie
        #echo "[DEBUG] Created currency with ID: " . $currency->getId() . " and name: " . $currency->getName() . "\n";

        $wallet = new \App\Entity\Wallet();
        $wallet->setName('Test Wallet');
        $wallet->setCurrency($currency);
        $wallet->setAuthor($user);
        $wallet->setSum(1000);
        $wallet->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $wallet->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));

        $walletRepository = static::getContainer()->get(\App\Repository\WalletRepository::class);
        $walletRepository->save($wallet, true);

        return $wallet;
    }

    /**
     * Create category for user.
     *
     * @param User $user User entity
     *
     * @return \App\Entity\Category
     */
    protected function createCategoryForUser(User $user): \App\Entity\Category
    {
        $category = new \App\Entity\Category();
        $category->setName('Test Category');
        $category->setAuthor($user);
        $category->setCreatedAt(new \DateTimeImmutable('2024-01-01'));
        $category->setUpdatedAt(new \DateTimeImmutable('2024-06-01'));

        $categoryRepository = static::getContainer()->get(\App\Repository\CategoryRepository::class);
        $categoryRepository->save($category, true);

        return $category;
    }

    /**
     * Create transaction for user.
     *
     * @param User $user User entity
     *
     * @return \App\Entity\Transaction
     */
    protected function createTransactionForUser(User $user): \App\Entity\Transaction
    {
        $wallet = $this->createWalletForUser($user);
        $category = $this->createCategoryForUser($user);

        $transaction = new \App\Entity\Transaction();
        $transaction->setName('Test Transaction');
        $transaction->setSum(100);
        $transaction->setValue(true);
        $transaction->setDescription('Test transaction description');
        $transaction->setAuthor($user);
        $transaction->setWallet($wallet);
        $transaction->setCategory($category);
        $transaction->setCreatedAt(new \DateTimeImmutable('2024-01-01'));

        $transactionRepository = static::getContainer()->get(\App\Repository\TransactionRepository::class);
        $transactionRepository->save($transaction, true);

        return $transaction;
    }



    protected function createCurrency( ): \App\Entity\Currency
    {
        $currency = new \App\Entity\Currency();
        $currency->setName('USD');
        $currencyRepository = static::getContainer()->get(\App\Repository\CurrencyRepository::class);
        $currencyRepository->add($currency, true);
        return $currency;
    }





} 