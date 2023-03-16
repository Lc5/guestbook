<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Comment;
use App\ImageOptimizer;
use App\Message\CommentMessage;
use App\Notification\CommentReviewNotification;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsMessageHandler]
class CommentMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SpamChecker $spamChecker,
        private readonly CommentRepository $commentRepository,
        private readonly MessageBusInterface $bus,
        private readonly WorkflowInterface $commentStateMachine,
        private readonly NotifierInterface $notifier,
        private readonly ImageOptimizer $imageOptimizer,
        #[Autowire('%photo_dir%')] private readonly string $photoDir,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(CommentMessage $message): void
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment instanceof Comment) {
            return;
        }

        if ($this->commentStateMachine->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = match ($score) {
                2 => 'reject_spam',
                1 => 'might_be_spam',
                default => 'accept',
            };
            $this->commentStateMachine->apply($comment, $transition);
            $this->entityManager->flush();
            $this->bus->dispatch($message);
        } elseif ($this->commentStateMachine->can($comment, 'publish') ||
            $this->commentStateMachine->can($comment, 'publish_ham')) {
            $notification = new CommentReviewNotification($comment, $message->getReviewUrl());
            $this->notifier->send($notification, ...$this->notifier->getAdminRecipients());
        } elseif ($this->commentStateMachine->can($comment, 'optimize')) {
            if ($comment->getPhotoFilename()) {
                $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
            }

            $this->commentStateMachine->apply($comment, 'optimize');
            $this->entityManager->flush();
        } elseif ($this->logger !== null) {
            $this->logger->debug(
                'Dropping comment message',
                ['comment' => $comment->getId(), 'state' => $comment->getState()]
            );
        }
    }
}
