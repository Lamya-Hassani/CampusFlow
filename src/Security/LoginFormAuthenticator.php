<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    // TargetPathTrait is a trait that provides a way to store the target path
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    // UrlGeneratorInterface is an interface that provides a way to generate URLs
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    // authenticate is a method that is called when the user submits the login form
    public function authenticate(Request $request): Passport
    {
        // get the email from the login form
        $email = $request->getPayload()->getString('email');

        // set the last username in the session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Passport is a class that represents the authentication process
        return new Passport(
            // UserBadge is a class that represents the user
            new UserBadge($email),
            // PasswordCredentials is a class that represents the password
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                // CsrfTokenBadge is a class that represents the CSRF token
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                // RememberMeBadge is a class that represents the remember me option
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // get the target path from the session
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        } elseif (in_array('ROLE_TEACHER', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('teacher_dashboard'));
        } elseif (in_array('ROLE_STUDENT', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('student_dashboard'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
