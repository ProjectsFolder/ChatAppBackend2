<?php

namespace App\Service;

use App\Entity\Chat;
use Doctrine\ORM\EntityManagerInterface;

class ChatService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    public function clearInactiveChats(int $inactive = 3600) {
        $chats = $this->em->getRepository(Chat::class)->findInactive($inactive);
        $return = count($chats) > 0;
        foreach ($chats as $chat) {
            foreach ($chat->getUsers() as $user) {
                foreach ($user->getMessages() as $message) {
                    $this->em->remove($message);
                }
                $this->em->remove($user);
            }
            $this->em->remove($chat);
        }
        $this->em->flush();
        return $return;
    }
}
