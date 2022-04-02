<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\Assert\Assert;

class GameController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route("/games", methods: ["POST"])]
    public function newGame(): Response
    {
        $game = new Game();
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $game->id], 201);
    }

    #[Route("/games/{id}/move", methods: ["POST"])]
    public function move(int $id, Request $request): Response
    {
        $game = $this->entityManager->find(Game::class, $id);
        if (null === $game) {
            throw new NotFoundHttpException();
        }

        try {
            $requestData = $request->toArray();
            Assert::keyExists($requestData, 'player');
            Assert::integer($requestData['player']);

            Assert::keyExists($requestData, 'horizontal');
            Assert::integer($requestData['horizontal']);

            Assert::keyExists($requestData, 'vertical');
            Assert::integer($requestData['vertical']);

            $game->move($requestData['player'], $requestData['horizontal'], $requestData['vertical']);

        } catch (\InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        $this->entityManager->flush();

        $data = [
            'winner' => $game->winner,
            'table' => $game->table()
        ];

        return new JsonResponse($data);
    }
}
