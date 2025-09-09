<?php

/**
 * Wallet Controller test.
 */

namespace App\Tests\Controller;

use PHPUnit\Framework\Assert;

/**
 * Class WalletControllerTest.
 */
class WalletControllerTest extends AbstractTestController
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/wallet';

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles              Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode Expected HTTP status code
     * @param string|null        $expectedRedirect   Expected redirect URL, if any
     */
    public function testIndexRoute(?array $roles, int $expectedStatusCode, ?string $expectedRedirect = null): void
    {
        $user = null;
        $wallet = null;

        if (null !== $roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $wallet = $this->createWalletForUser($user);
            $this->assertNotNull($wallet->getId());
        }

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE);
        $response = $this->httpClient->getResponse();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if (null !== $expectedRedirect) {
            $this->assertTrue($response->isRedirect());
            $this->assertSame($expectedRedirect, $response->headers->get('Location'));

            return;
        }

        $this->assertNavbar();

        $this->assertSelectorExists('table thead');
        $this->assertCount(8, $crawler->filter('table thead th'));
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
        $crawler->filter('table tbody tr')->each(function ($row) {
            Assert::assertCount(8, $row->filter('td'));
            $this->assertDropdownMenu($row);
        });

        $this->assertCreateLink($crawler, '/wallet/create');
    }

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles              Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode Expected HTTP status code
     * @param string|null        $expectedRedirect   Expected redirect URL, if any
     */
    public function testShowWallet(?array $roles, int $expectedStatusCode, ?string $expectedRedirect = null): void
    {
        $user = null;
        $wallet = null;

        if (null !== $roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $wallet = $this->createWalletForUser($user);
        }

        $walletId = $wallet ? $wallet->getId() : 999;
        $crawler = $this->httpClient->request('GET', '/wallet/'.$walletId);

        $response = $this->httpClient->getResponse();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if (null !== $expectedRedirect) {
            $this->assertTrue($response->isRedirect($expectedRedirect));

            return;
        }

        if (null !== $wallet) {
            $this->assertNavbar();

            $this->assertSelectorExists('dl.dl-horizontal');

            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(1)', (string) $wallet->getId());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(2)', $wallet->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(3)', $wallet->getCurrency()->getName());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(4)', $wallet->getCreatedAt()->format('Y/m/d'));
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(5)', $wallet->getUpdatedAt()->format('Y/m/d'));

            $this->assertBackToList();
        }
    }

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles                    Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode       Expected HTTP status code
     * @param string|null        $expectedRedirectLocation Expected redirect URL, if any
     */
    public function testCreateAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        $currency = null;

        if (null !== $roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $currency = $this->createCurrency();
        }

        $crawler = $this->httpClient->request('GET', '/wallet/create');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList();

            $form = $crawler->filter('form')->form([
                'wallet[name]' => 'Test Wallet',
                'wallet[sum]' => 1000,
                'wallet[currency]' => $currency->getId(),
            ]);

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/wallet');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles                    Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode       Expected HTTP status code
     * @param string|null        $expectedRedirectLocation Expected redirect URL, if any
     */
    public function testEditAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        if (null !== $roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $wallet = $this->createWalletForUser($user);
            $walletId = $wallet->getId();
        } else {
            $walletId = 1;
        }

        $crawler = $this->httpClient->request('GET', '/wallet/'.$walletId.'/edit');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList($crawler);

            $form = $crawler->filter('form')->form([
                'wallet[name]' => 'Updated Test Wallet',
                'wallet[sum]' => 2000,
            ]);

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/wallet');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles                    Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode       Expected HTTP status code
     * @param string|null        $expectedRedirectLocation Expected redirect URL, if any
     */
    public function testDeleteAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        if (null !== $roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $wallet = $this->createWalletForUser($user);
            $walletId = $wallet->getId();
        } else {
            $walletId = 1;
        }

        $crawler = $this->httpClient->request('GET', '/wallet/'.$walletId.'/delete');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList();

            $form = $crawler->filter('form')->form();

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/wallet');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }
}
