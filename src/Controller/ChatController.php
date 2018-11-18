<?php

namespace App\Controller;

use App\Entity\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/api/chat")
 */
class ChatController extends AbstractController
{
    /**
     * @Route("/create/{name}", methods={"POST"}, name="chat.create")
     */
    public function create(ValidatorInterface $validator, string $name)
    {
        $em = $this->getDoctrine()->getManager();

        do {
            $secret = bin2hex(random_bytes(30));
        } while ($em->getRepository(Chat::class)->findOneBy(['secret' => $secret]) != null);

        $active = time();

        $chat = new Chat();
        $chat->setName($name);
        $chat->setSecret($secret);
        $chat->setActive($active);

        $errors = $validator->validate($chat);
        if (count($errors) > 0) {
            $result = array();
            foreach ($errors as $error) {
                $result[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($result, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($chat);
        $em->flush();

        return $this->json([
            'name'   => $name,
            'key'    => $secret,
            'active' => $active
        ]);
    }

    /**
     * @Route("/exists/{key}", methods={"GET"}, name="chat.exists")
     */
    public function exists(string $key)
    {
        $em = $this->getDoctrine()->getManager();
        $chat = $em->getRepository(Chat::class)->findOneBy(['secret' => $key]);

        if ($chat === null) {
            return new JsonResponse(['message' => 'Chat not found'],
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'name'   => $chat->getName(),
            'key'    => $key,
            'active' => $chat->getActive()
        ]);
    }

    /**
     * @Route("/refresh/{key}", methods={"PUT"}, name="chat.refresh")
     * @IsGranted("ROLE_USER")
     */
    public function updateActive(string $key)
    {
        $active = time();

        $chat = $this->getUser()->getChat();
        $chat->setActive($active);

        $em = $this->getDoctrine()->getManager();
        $em->persist($chat);
        $em->flush();

        return $this->json([
            'name'   => $chat->getName(),
            'key'    => $key,
            'active' => $active
        ]);
    }
}
