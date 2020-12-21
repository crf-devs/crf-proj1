<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Repository\UserRepository;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use PantherExtension\Driver\PantherDriver;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityContext extends RawMinkContext
{
    use MinkContextTrait;

    private UserRepository $userRepository;
    private SessionInterface $session;

    public function __construct(UserRepository $userRepository, SessionInterface $session)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    /**
     * @Given I am authenticated as :username
     */
    public function login(string $username): void
    {
        $user = $this->userRepository->loadUserByUsername($username);
        if (!$user) {
            throw new UsernameNotFoundException(\sprintf('%s is not a valid User.', $username));
        }

        /** @var BrowserKitDriver|PantherDriver $driver */
        $driver = $this->getSession()->getDriver();

        if ($driver instanceof PantherDriver) {
            $this->loginForPanther($user);

            return;
        }

        $this->session->set(
            '_security_main',
            serialize(new UsernamePasswordToken($user, null, 'main', $user->getRoles()))
        );
        $this->session->save();

        $driver->getClient()->getCookieJar()->set(new Cookie($this->session->getName(), $this->session->getId()));
    }

    /**
     * @throws ExpectationException
     */
    private function loginForPanther(UserInterface $user): void
    {
        $minkContext = $this->getMinkContext();
        try {
            $minkContext->visit('/login');
            $minkContext->fillField('user_login[identifier]', $user->getUsername());
            $minkContext->fillField('user_login[password]', 'covid19');
            $minkContext->pressButton('Je me connecte');
            $minkContext->assertPageAddress('/');
        } catch (\Exception $exception) {
            throw new ExpectationException(sprintf('Impossible to connect user: %s', $exception->getMessage()), $this->getSession(), $exception);
        }
    }
}
