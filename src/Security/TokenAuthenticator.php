<?php

namespace App\Security;

use App\Entity\Chat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['message' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request)
    {
        $route = $request->attributes->get('_route');
        $supports = 'chat.create' !== $route
            && 'user.login' !== $route
            && isset($request->attributes->get('_route_params')['key'])
            && $request->headers->has('X-AUTH-TOKEN');
        return $supports;
    }

    public function getCredentials(Request $request)
    {
        return array(
            'token' => $request->headers->get('X-AUTH-TOKEN'),
            'key'   => $request->attributes->get('_route_params')['key'],
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = $credentials['token'];
        $key   = $credentials['key'];

        if (null === $token || null === $key) {
            return null;
        }

        $chat = $this->em->getRepository(Chat::class)->findOneBy(['secret' => $key]);

        if (null === $chat) {
            return null;
        }

        return $this->em->getRepository(User::class)->findOneBy(['token' => $token, 'chat' => $chat]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['message' => strtr($exception->getMessageKey(), $exception->getMessageData())],
            Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
