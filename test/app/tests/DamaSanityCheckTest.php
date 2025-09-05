<?php



namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DamaSanityCheckTest extends KernelTestCase
{
public function testDatabaseIsResetBetweenTests(): void
{
self::bootKernel();

$em = self::getContainer()->get(EntityManagerInterface::class);

$repo = $em->getRepository(User::class);

// Sprawdź, czy nie istnieje user@example.com
$user = $repo->findOneBy(['email' => 'user@example.com']);
$this->assertNull($user);

// Dodaj usera
$user = new User();
$user->setEmail('user@example.com');
$user->setPassword('123456');
$em->persist($user);
$em->flush();

// Potwierdź, że się zapisał
$this->assertNotNull($repo->findOneBy(['email' => 'user@example.com']));
}

public function testShouldBeEmptyAgain(): void
{
self::bootKernel();

$em = self::getContainer()->get(EntityManagerInterface::class);

$repo = $em->getRepository(User::class);

// Jeśli DAMA działa, to user@example.com już NIE powinien istnieć
$this->assertNull($repo->findOneBy(['email' => 'user@example.com']));
}
}
