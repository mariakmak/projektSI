<?php
/**
 * Category Controller test.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Symfony\Component\DomCrawler\Crawler;
use PHPUnit\Framework\Assert;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends AbstractTestController
{
    /**
     * Test route.
     *
     * @const string
     */
    public const TEST_ROUTE = '/category';



    /**
     * @dataProvider roleProvider
     */
    public function testIndexRoute(
        ?array $roles,
        int $expectedStatusCode,
        ?string $expectedRedirect = null
    ): void {

        $user = null;
        $category = null;

        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
            $category = $this->createCategoryForUser($user);
            $this->assertNotNull($category->getId());
        }

        $crawler = $this->httpClient->request('GET', self::TEST_ROUTE);
        $response = $this->httpClient->getResponse();
        #echo $this->httpClient->getResponse()->getContent();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if ($expectedRedirect !== null) {
            $this->assertTrue($response->isRedirect());
            $this->assertSame($expectedRedirect, $response->headers->get('Location'));
            return;
        }

        //menu
        $this->assertNavbar();

        // struktura tabeli
        $this->assertSelectorExists('table thead');
        $this->assertCount(6, $crawler->filter('table thead th'));
        $this->assertGreaterThan(0, $crawler->filter('table tbody tr')->count());
        $crawler->filter('table tbody tr')->each(function ($row) {
            Assert::assertCount(6, $row->filter('td'));
            $this->assertDropdownMenu($row);
        });

        #echo $this->httpClient->getResponse()->getContent();
        // link category.create
        $this->assertCreateLink($crawler, '/category/create');
        $link = $crawler->selectLink($this->translator->trans('action.create'))->link();
        $crawler = $this->httpClient->click($link);
        $this->assertSame(200, $this->httpClient->getResponse()->getStatusCode());
        $this->assertSelectorExists('form');
    }






    /**
     * @dataProvider roleProvider
     */
    public function testShowCategory(?array $roles, int $expectedStatusCode, ?string $expectedRedirect = null): void
    {
        $user = null;
        $category = null;

        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);

            $category = $this->createCategoryForUser($user);
        }

        $categoryId = $category ? $category->getId() : 999;
        $crawler = $this->httpClient->request('GET', '/category/' . $categoryId);

        $response = $this->httpClient->getResponse();
        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        if ($expectedRedirect !== null) {
            $this->assertTrue($response->isRedirect($expectedRedirect));
            return;
        }

        if ($category !== null) {

            //menu
            $this->assertNavbar();

            $this->assertSelectorExists('dl.dl-horizontal');

            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(1)', (string)$category->getId());
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(2)', $category->getCreatedAt()->format('Y/m/d'));
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(3)', $category->getUpdatedAt()->format('Y/m/d'));
            $this->assertSelectorTextContains('dl.dl-horizontal dd:nth-of-type(4)', $category->getName());

            $this->assertBackToList($crawler);

        }

    }





    /**
     * @dataProvider roleProvider
     */
    public function testCreateAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);
        }

        $crawler = $this->httpClient->request('GET', '/category/create');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList($crawler);

            #$submitLabel = $this->translator->trans('action.save');
            #echo "Submit label in test: " . $submitLabel;

            $form = $crawler->filter('form')->form([
                'category[name]' => 'Nowa kategoria testowa',
            ]);

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/category');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }





    /**
     * @dataProvider roleProvider
     */
    public function testEditAction(?array $roles, int $expectedStatusCode, ?string $expectedRedirectLocation): void
    {
        if ($roles !== null) {
            $user = $this->createUser($roles);
            $this->httpClient->loginUser($user);

            $category = $this->createCategoryForUser($user);
            $categoryId = $category->getId();
        }else {

            $categoryId = 1;
        }

        $crawler = $this->httpClient->request('GET', '/category/' . $categoryId . '/edit');

        if ($expectedRedirectLocation) {
            $this->assertResponseRedirects($expectedRedirectLocation);
            $this->assertResponseStatusCodeSame($expectedStatusCode);
        } else {
            $this->assertResponseStatusCodeSame($expectedStatusCode);

            $this->assertNavbar();
            $this->assertSelectorExists('form');
            $this->assertBackToList($crawler);


            #$submitLabel = $this->translator->trans('action.edit');
            #echo "Submit label in test: " . $submitLabel;

            $form = $crawler->filter('form')->form([
                'category[name]' => 'Nowa kategoria testowa',
            ]);

            $submitCount = $crawler->filter('form input[type="submit"]')->count();
            $this->assertSame(1, $submitCount);
            $this->httpClient->submit($form);
            $this->assertResponseRedirects('/category');
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

            $category = $this->createCategoryForUser($user);
            $categoryId = $category->getId();
        } else {
            $categoryId = 1;
        }


        $crawler = $this->httpClient->request('GET', '/category/' . $categoryId . '/delete');

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
            $this->assertResponseRedirects('/category');
            $this->httpClient->followRedirect();
            $this->assertSelectorExists('.alert-success');
        }
    }


















}


