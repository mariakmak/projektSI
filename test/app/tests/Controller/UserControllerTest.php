<?php

/**
 * user controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends AbstractTestController
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/user';

    /**
     * Provides roles and expected outcomes for the admin panel (/user).
     *
     * - Unauthenticated user: 302 redirect to login
     * - Regular user: 403 forbidden
     * - Admin user: 200 OK
     *
     * @return array<string, array{0: ?array<string>, 1: int, 2: ?string}> Role sets and expected outcomes
     */
    public function roleProvider(): array
    {
        return [
            'unauthenticated user' => [null, 302, '/login'],
            'regular user' => [[UserRole::ROLE_USER->value], 403, null],
            'admin user' => [[UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value], 200, null],
        ];
    }

    /**
     * @dataProvider roleProvider
     *
     * @param array<string>|null $roles              Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode Expected HTTP status code
     * @param string|null        $expectedRedirect   Expected redirect URL, if any
     */
    public function testIndexRoute(?array $roles, int $expectedStatusCode, ?string $expectedRedirect): void
    {
        $user = null;
        if ($roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
        }

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $response = $this->httpClient->getResponse();
        // DEBUG OUTPUT
        // echo "\n[DEBUG] Status code: " . $response->getStatusCode() . "\n";
        // echo "[DEBUG] Location: " . $response->headers->get('Location') . "\n";
        // echo "[DEBUG] Content: " . mb_substr($response->getContent(), 0, 500) . "\n";

        $result = $response->getStatusCode();

        $this->assertEquals($expectedStatusCode, $result);
        if ($expectedRedirect) {
            $this->assertResponseRedirects($expectedRedirect);
        }

        if ($roles && in_array('ROLE_ADMIN', $roles, true) && 200 === $expectedStatusCode) {
            $h1 = $this->httpClient->getCrawler()->filter('h1')->text();
            $expected = $this->translator->trans('title.user_list');
            // echo "\n[DEBUG] H1: " . $h1 . "\n";
            // echo "[DEBUG] Expected H1: " . $expected . "\n";
            $this->assertSelectorTextContains('h1', $expected);
            // ...other admin-specific assertions can go here...
        }
    }

    /**
     * Test index action displays users table.
     */
    public function testIndexTable(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $crawler = $this->httpClient->getCrawler();

        // DEBUG: nagłówki th
        $ths = $crawler->filter('th');
        //        foreach ($ths as $i => $th) {
        //            echo "\n[DEBUG] TH[$i]: " . trim($th->textContent) . "\n";
        //        }


        $expected = [
            $this->translator->trans('label.id'),
            $this->translator->trans('label.email'),
            $this->translator->trans('label.roles'),
            $this->translator->trans('label.actions'),
        ];
        foreach ($expected as $i => $label) {
            //            echo "[DEBUG] Expected TH[$i]: $label\n";
            $this->assertEquals($label, trim($ths->eq($i)->text()));
        }
    }

    /**
     * Test index action displays user data and dropdown menu for admin.
     */
    public function testIndexData(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $this->assertResponseIsSuccessful();

        $crawler = $this->httpClient->getCrawler();

        $firstRow = $crawler->filter('table tbody tr')->first();

        // DEBUG: wypisz zawartość każdej komórki
        //        foreach ($firstRow->filter('td') as $i => $cell) {
        //            echo "\n[DEBUG] Cell[$i]: " . trim($cell->textContent) . "\n";
        //        }

        $this->assertEquals((string) $user->getId(), trim($firstRow->filter('td')->eq(0)->text()));
        $this->assertEquals($user->getEmail(), trim($firstRow->filter('td')->eq(1)->text()));
        $this->assertStringContainsString('ROLE_ADMIN', trim($firstRow->filter('td')->eq(2)->text()));

        // dropdown menu
        $rows = $crawler->filter('table tbody tr');
        $this->assertGreaterThan(0, $rows->count());

        $firstRow = $rows->first();
        $this->assertDropdownMenu($firstRow, 1); // Only edit action for users

        // link/edit
        $editLink = $firstRow->filter('.dropdown-menu a[href*="/edit"]');
        $this->assertGreaterThan(0, $editLink->count(), 'Edit link not found in dropdown menu');
        $this->assertStringContainsString('/edit', $editLink->attr('href'));
    }

    /**
     * Test index action displays pagination.
     */
    public function testIndexActionDisplaysPagination(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        for ($i = 1; $i <= 25; ++$i) {
            $this->createUserWithEmail([UserRole::ROLE_USER->value], "user{$i}@example.com");
        }

        $this->httpClient->request('GET', self::TEST_ROUTE);
        $this->assertResponseIsSuccessful();

        $crawler = $this->httpClient->getCrawler();
        $this->assertSelectorExists('.navigation');
    }

    /**
     * Test route for edit action.
     *
     * @param array<string>|null $roles              Roles of the user (null for unauthenticated)
     * @param int                $expectedStatusCode Expected HTTP status code
     * @param string|null        $expectedRedirect   Expected redirect URL, if any
     *
     * @dataProvider roleProvider
     */
    public function testEditRoute(?array $roles, int $expectedStatusCode, ?string $expectedRedirect): void
    {
        $user = null;
        if ($roles) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
        }

        // Create a test user to edit
        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $result = $this->httpClient->getResponse()->getStatusCode();

        $this->assertEquals($expectedStatusCode, $result);
        if ($expectedRedirect) {
            $this->assertResponseRedirects($expectedRedirect);
        }
    }

    /**
     * Test edit action for admin user.
     */
    public function testEditActionForAdminUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->translator->trans('title.user_edit', ['%id%' => $testUser->getId()]));
    }

    /**
     * Test edit action displays form.
     */
    public function testEditActionDisplaysForm(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $crawler = $this->httpClient->getCrawler();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="user[email]"]');
        $this->assertSelectorExists('input[name="user[password][first]"]');
        $this->assertSelectorExists('input[name="user[password][second]"]');
        $this->assertSelectorExists('select[name="user[roles][]"]');
    }

    /**
     * Test edit action form.
     */
    public function testEditActionSubmitWithPassword(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $this->httpClient->submitForm(
            $this->translator->trans('action.edit'),
            [
                'user[email]' => 'updated@example.com',
                'user[password][first]' => 'newpassword123',
                'user[password][second]' => 'newpassword123',
                'user[roles]' => [UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value],
            ]
        );

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $this->translator->trans('message.created_successfully'));
    }

    /**
     * Test edit action form submission without password change.
     */
    public function testEditActionSubmitWithoutPassword(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $this->httpClient->submitForm(
            $this->translator->trans('action.edit'),
            [
                'user[email]' => 'updated@example.com',
                'user[password][first]' => '',
                'user[password][second]' => '',
                'user[roles]' => [UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value],
            ]
        );

        $this->assertResponseRedirects(self::TEST_ROUTE);
        $this->httpClient->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $this->translator->trans('message.created_successfully'));
    }

    /**
     * Test edit action form validation.
     */
    public function testEditActionFormValidation(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $this->httpClient->submitForm(
            $this->translator->trans('action.edit'),
            [
                'user[email]' => 'invalid-email',
                'user[password][first]' => 'newpassword123',
                'user[password][second]' => 'differentpassword',
                'user[roles]' => [UserRole::ROLE_USER->value],
            ]
        );

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    /**
     * Test edit action back to list functionality.
     */
    public function testEditActionBackToList(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $testUser = $this->createUserWithEmail([UserRole::ROLE_USER->value], 'user1@example.com');

        $this->httpClient->request('GET', self::TEST_ROUTE.'/'.$testUser->getId().'/edit');
        $this->assertResponseIsSuccessful();

        $this->assertBackToList();
    }

    /**
     * Test edit action with non-existent user.
     */
    public function testEditActionWithNonExistentUser(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);

        $this->httpClient->request('GET', self::TEST_ROUTE.'/999999/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Test index and edit pages have navbar for admin.
     */
    public function testNavbar(): void
    {
        $user = $this->createUser([UserRole::ROLE_ADMIN->value, UserRole::ROLE_USER->value]);
        $this->httpClient->loginUser($user);


        $pages = [
            'index' => self::TEST_ROUTE,
            'edit' => self::TEST_ROUTE.'/'.$user->getId().'/edit',
        ];

        foreach ($pages as $pageName => $url) {
            $this->httpClient->request('GET', $url);
            $this->assertResponseIsSuccessful();
            $this->assertNavbar();
        }
    }
}
