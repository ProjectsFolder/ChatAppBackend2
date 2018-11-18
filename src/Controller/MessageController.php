<?php

namespace App\Controller;

use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * @Route("/api/message")
 */
class MessageController extends AbstractController
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @Route("/{key}", methods={"GET"}, name="message.messages")
     * @IsGranted("ROLE_USER")
     */
    public function getMessages(string $key)
    {
        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository(Message::class)->findAllByChatSecret($key);
        return $this->json($messages);
    }

    /**
     * @Route("/{id}/{key}", methods={"GET"}, requirements={"id"="\d+"}, name="message.message")
     * @IsGranted("ROLE_USER")
     */
    public function getMessage(int $id, string $key)
    {
        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository(Message::class)->findOneByMessageIdAndChatSecret($id, $key);

        if ($message === null) {
            return new JsonResponse(['message' => 'Message not found'],
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        return $this->json($message);
    }

    /**
     * @Route("/{key}", methods={"POST"}, name="message.save")
     * @IsGranted("ROLE_USER")
     */
    public function saveMessage(Request $request)
    {
        $user = $this->getUser();
        $text = $request->get('text', '');
        $now = time();

        $em = $this->getDoctrine()->getManager();

        $message = new Message();
        $message->setText($text);
        $message->setTimecreated($now);
        $message->setUser($user);

        $errors = $this->validateMessage($message);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($message);

        $chat = $user->getChat();
        $chat->setActive($now);
        $em->persist($chat);

        $em->flush();

        return $this->json([
            'id'          => $message->getId(),
            'text'        => $message->getText(),
            'timecreated' => $message->getTimecreated(),
            'author'      => $message->getUser()->getName()
        ]);
    }

    /**
     * @Route("/{id}/{key}", methods={"PUT"}, requirements={"id"="\d+"}, name="message.update")
     * @IsGranted("ROLE_USER")
     */
    public function updateMessage(Request $request, int $id)
    {
        $user = $this->getUser();
        $text = $request->get('text', '');
        $now = time();

        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository(Message::class)->find($id);

        if ($message === null) {
            return new JsonResponse(['message' => 'Message not found'],
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        if ($message->getUser()->getId() != $user->getId()) {
            return new JsonResponse(['message' => 'No permissions'],
                \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $message->setText($text);

        $errors = $this->validateMessage($message);
        if (count($errors) > 0) {
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($message);

        $chat = $user->getChat();
        $chat->setActive($now);
        $em->persist($chat);

        $em->flush();

        return $this->json([
            'id'          => $message->getId(),
            'text'        => $message->getText(),
            'timecreated' => $message->getTimecreated(),
            'author'      => $message->getUser()->getName()
        ]);
    }

    /**
     * @Route("/{id}/{key}", methods={"DELETE"}, requirements={"id"="\d+"}, name="message.delete")
     * @IsGranted("ROLE_USER")
     */
    public function deleteMessage(int $id)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $message = $em->getRepository(Message::class)->find($id);

        if ($message === null) {
            return new JsonResponse(['message' => 'Message not found'],
                \Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
        }

        if ($message->getUser()->getId() != $user->getId()) {
            return new JsonResponse(['message' => 'No permissions'],
                \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
        }

        $em->remove($message);

        $chat = $user->getChat();
        $chat->setActive(time());
        $em->persist($chat);

        $em->flush();

        return $this->json([
            'id'          => $id,
            'text'        => $message->getText(),
            'timecreated' => $message->getTimecreated(),
            'author'      => $message->getUser()->getName()
        ]);
    }

    private function validateMessage($message)
    {
        $result = array();
        $errors = $this->validator->validate($message);
        if (count($errors) > 0) {
            $result = array();
            foreach ($errors as $error) {
                $result[$error->getPropertyPath()] = $error->getMessage();
            }
        }
        return $result;
    }
}
