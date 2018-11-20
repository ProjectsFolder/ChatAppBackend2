<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/login/{key}", methods={"POST"}, name="user.login")
     */
    public function __invoke(Request $request, ValidatorInterface $validator, string $key)
    {
        $em = $this->getDoctrine()->getManager();
        $chat = $em->getRepository(Chat::class)->findOneBy(['secret' => $key]);
        if ($chat) {
            $username = $request->get('name', '');
            $login_is_used = $em->getRepository(User::class)
                    ->findOneBy(['name' => $username, 'chat' => $chat]) != null;

            if ($login_is_used) {
                return new JsonResponse(['name' => 'Username is used'], Response::HTTP_BAD_REQUEST);
            }

            do {
                $token = bin2hex(random_bytes(30));
            } while ($em->getRepository(User::class)->findOneBy(['token' => $token]) != null);

            $user = new User();
            $user->setName($username);
            $user->setToken($token);
            $user->setChat($chat);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                $result = array();
                foreach ($errors as $error) {
                    $result[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse($result, Response::HTTP_BAD_REQUEST);
            }

            $em->persist($user);

            $chat->setActive(time());
            $em->persist($chat);

            $em->flush();

            return $this->json([
                'key'      => $key,
                'username' => $username,
                'token'    => $token,
                'chatname' => $chat->getName()
            ]);
        }

        return new JsonResponse(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
    }
}
