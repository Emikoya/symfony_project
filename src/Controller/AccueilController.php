<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Reponse;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_accueil')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $messages = $doctrine->getRepository(Message::class)->findAll();
        $users = $doctrine->getRepository(User::class)->findAll();
        $reponses = $doctrine->getRepository(Reponse::class)->findAll();

        return $this->render('accueil/index.html.twig', [
            'messages' => $messages,
            'users' => $users,
            'reponses' => $reponses,
        ]);
    }

    /**
     * @Route("/post", name="app_post", methods={"POST"})
     */
    public function post(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $request->request->get('mail')]);

        $message = new Message();
        $message->setMessage($request->request->get('post'));
        $message->setAuteur($user);

        $entityManager->persist($message);
        $entityManager->flush();

        return new RedirectResponse($this->generateUrl('app_accueil'));
    }

    /**
     * @Route("/comment", name="app_comment", methods={"POST"})
     */
    public function comment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userRepository = $entityManager->getRepository(User::class);
        $messageRepository = $entityManager->getRepository(Message::class);
        $user = $userRepository->findOneBy(['email' => $request->request->get('mail')]);
        $message = $messageRepository->findOneBy(['id' => $request->request->get('message')]);

        var_dump($request->request->get('message'));

        $reponse = new Reponse();
        $reponse->setReponse($request->request->get('comment'));
        $reponse->setMessage($message);
        $reponse->setAuteur($user);

        $entityManager->persist($reponse);
        $entityManager->flush();

        return new RedirectResponse($this->generateUrl('app_accueil'));
    }

    /**
     * @Route("/api", name="app_api")
     */
    public function api()
    {
        $city = 'Calais';
        $key = '19dd3499a90fec0e7ec5f8ad05d8a0bd';

        $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . $city . '&appid=' . $key;

        $data = json_decode(file_get_contents($url), true);

        $temperaturef = $data['main']['temp'];

        $temp = $temperaturef - 273.15;

        return new JsonResponse("temperature a " . $city . " de " . $temp);
    }
}
