<?php
/**
 * Transaction Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Entity\Category;
use App\Entity\User;
use App\Entity\Currency;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Repository\CategoryRepository;
use App\Repository\CurrencyRepository;
use Symfony\Component\DomCrawler\Crawler;
use PHPUnit\Framework\Assert;

/**
 * Class TransactionControllerTest.
 */
class TransactionControllerTest extends AbstractTestController
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/transaction';

    /**
     * @dataProvider roleProvider
     */
    public function testIndexRoute(
        ?array $roles,
        int $expectedStatusCode,
        ?string $expectedRedirect = null
    ): void {
        $user = null;
        $transaction = null;

        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $transaction = $this->createTransactionForUser($user);
            $this->assertNotNull($transaction->getId());
        }

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE);
        $response = $this->httpClient->getResponse();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if ($expectedRedirect !== null) {
            $this->assertTrue($response->isRedirect());
            $this->assertSame($expectedRedirect, $response->headers->get('Location'));
            return;
        }

        $this->assertNavbar();
        $this->assertSelectorExists('table thead');
        $this->assertCount(11, $crawler->filter('table thead th'));
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
        $crawler->filter('table tbody tr')->each(function ($row) {
            Assert::assertCount(11, $row->filter('td'));
            $this->assertDropdownMenu($row, 2);
        });
        $this->assertCreateLink($crawler, '/transaction/create');

    }

    /**
     * @dataProvider roleProvider
     */
    public function testShowTransaction(?array $roles, int $expectedStatusCode, ?string $expectedRedirect = null): void
    {
        $user = null;
        $transaction = null;

        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $transaction = $this->createTransactionForUser($user);
        }

        $transactionId = $transaction ? $transaction->getId() : 999;
        $crawler = $this->httpClient->request('GET', '/transaction/' . $transactionId);

        $response = $this->httpClient->getResponse();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if ($expectedRedirect !== null) {
            $this->assertTrue($response->isRedirect($expectedRedirect));
            return;
        }

        if ($transaction !== null) {

            $this->assertNavbar();


            $this->assertSelectorExists('dl.dl-horizontal');

            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(1)', (string)$transaction->getId());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(2)', $transaction->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(3)', $transaction->getCategory()->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(4)', $transaction->getDescription());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(5)', $transaction->getCreatedAt()->format('Y/m/d'));
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(6)', $transaction->getWallet()->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(7)', (string)$transaction->getSum());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(8)', $transaction->getWallet()->getCurrency()->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(9)', (string)$transaction->getValue());

            $this->assertBackToList($crawler);
        }
    }

    /**
     * @dataProvider roleProvider
     */
    public function testCreateAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        $category = null;
        $wallet = null;
        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $category = $this->createCategoryForUser($user);
            $wallet = $this->createWalletForUser($user);
        }

        $crawler = $this->httpClient->request('GET', '/transaction/create');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList();

            $formData = [
                'transaction[name]' => 'Test Transaction',
                'transaction[sum]' => 100,
                'transaction[value]' => true,
                'transaction[description]' => 'Test description',
            ];
            if ($category !== null) {
                $formData['transaction[category]'] = $category->getId();
            }
            if ($wallet !== null) {
                $formData['transaction[wallet]'] = $wallet->getId();
            }

            $form = $crawler->filter('form')->form($formData);

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/transaction');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }

    /**
     * @dataProvider roleProvider
     */
    public function testDeleteAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $transaction = $this->createTransactionForUser($user);
            $transactionId = $transaction->getId();
        } else {
            $transactionId = 1;
        }

        $crawler = $this->httpClient->request('GET', '/transaction/' . $transactionId . '/delete');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList($crawler);

            $form = $crawler->filter('form')->form();

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/transaction');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }


}

