<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Sessions;
use App\Form\AjoutSessionType;
use Symfony\Component\HttpFoundation\Response;
use app\Entity\Users;

use Symfony\Component\HttpFoundation\JsonResponse;

class SessionController extends AbstractController
{
    /**
     * @Route("/descriptionSession", name="descriptionSession")
     */
    public function index()
    {
        return $this->render('session/descriptionSession.html.twig', [
            'controller_name' => 'SessionController',
        ]);
    }


    /* AJOUT ET MODIFICATION D'UNE SESSION */

    /**
     * @Route("/ajoutSession", name="ajoutSession")
     * @Route("/ajoutSession/{id}/edit", name="modifSession")
     */
    public function AjoutSession(Sessions $session = null, Request $request, EntityManagerInterface $manager)
    {
        
        $idUser = $this->getUser()->getId();

        if ($session->getUser()->getId() != $idUser) {
            return $this->redirectToRoute('mesSessions');
        }
     
        if (!$session) {
            $session = new Sessions();
        }

        $sessions = $this->getDoctrine()->getRepository(Sessions::class)->findAll();
        $form = $this->createForm(AjoutSessionType::class, $session);
        $session->setUser($this->getUser());
        $session->setNbParticipants("1");
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Upload a file 
            $file = $form->get('image')->getData();
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('brochures_directory'), $filename);
            $session->setImage($filename);

            $manager->persist($session);
            $manager->flush();
            return $this->redirectToRoute('mesSessions');
        }
        return $this->render('session/ajoutSession.html.twig', [
            'form' => $form->createView(),
            'editMode' => $session->getId() !== null,
            'sessions' => $sessions
        ]);
    }

    /* SUPPRESSION D'UNE SESSION */

    /**
     * @Route("/ajoutSession/{id}/delete", name="deleteSession")
     */
    public function deleteSession(Sessions $session)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($session);
        $em->flush();
        return $this->redirectToRoute('mesSessions');
    }

    /* AFFICHAGE DE TOUTES LES SESSIONS LIEES A L'UTILISATEUR */

    /**
     * @Route("/mesSessions", name="mesSessions")
     */
    public function getAllSession()
    {
        $idUser = $this->getUser()->getId();
        $sessions = $this->getDoctrine()->getRepository(Sessions::class)->findBy(array('user' => $idUser));

        
        $findUsers = $this->getDoctrine()->getRepository(Users::class)->findOneBy(array('id' => $idUser));
        $users = $findUsers->getParticipe();

        return $this->render('session/mesSessions.html.twig', [
            'sessions' => $sessions,
            'users' => $users,
        ]);
    }

    /* AFFICHE DE LA DESCRIPTION D'UNE SESSION */

    /**
     * @Route("/descriptionSession/{id}/show", name="showSession")
     */
    public function getSession($id)
    {
        $ownSession = false;

        $idUser = $this->getUser()->getId();

        $session = $this->getDoctrine()->getRepository(Sessions::class)->findOneBy(array('id' => $id));
        
        $participe = $session->getUsers();
        $bool = false;
        if ($idUser == $session->getUser()->getId()) {
            $ownSession = true;
        }

        foreach ($participe as $user) {
            if ($user->getId() == $idUser) {
                $bool = true;
            } else {
                $bool = false;
            }
        }

        return $this->render('session/descriptionSession.html.twig', [
            'session' => $session,
            'editMode' => $bool !== false,
            'ownSession' => $ownSession !== false,
        ]);
    }


    /**
     * @Route("/sessionsAmis", name="sessionsAmis")
     */
    public function findSessionsAmis()
    {
        $amis = $this->getDoctrine()->getRepository(Sessions::class)->findBy(array('type' => 'Session entre amis'));

        return $this->render('session/sessionsAmis.html.twig', [
            'amis' => $amis,
        ]);
    }

    /**
     * @Route("/sessionsCoach", name="sessionsCoach")
     */
    public function findSessionsCoach()
    {
        $coachs = $this->getDoctrine()->getRepository(Sessions::class)->findBy(array('type' => 'Session avec un coach'));

        return $this->render('session/sessionsCoach.html.twig', [
            'coachs' => $coachs,
        ]);
    }

    /**
     * @Route("/evenementSportif", name="evenementSportif")
     */
    public function findEvenementSportif()
    {
        $sportifs = $this->getDoctrine()->getRepository(Sessions::class)->findBy(array('type' => 'Événement sportif'));

        return $this->render('session/evenementSportif.html.twig', [
            'sportifs' => $sportifs,
        ]);
    }

    /**
     * @Route("/evenementAsso", name="evenementAsso")
     */
    public function findEvenementAsso()
    {
        $assos = $this->getDoctrine()->getRepository(Sessions::class)->findBy(array('type' => 'Événement associatif'));

        return $this->render('session/evenementAsso.html.twig', [
            'assos' => $assos,
        ]);
    }

    /**
     * @Route("/rejoind-session/{id}", name="rejoind")
     * Rejoindre une session
     * @author Kevin Broucquart
     */
    public function rejoindSession(Request $request, EntityManagerInterface $manager, $id)
    {
        $session = $this->getDoctrine()->getRepository(Sessions::class)->findOneBy(array('id' => $id));
        $nbMax = $session->getNbMax();
        $participants = $session->getUsers();
        $nbInscris = sizeof($participants);

        if ($nbInscris < $nbMax) {

            if ($request->isXMLHttpRequest()) {

                //Instanciation  de l'entity qui regroupe l'id Session et l'id User
                $user = $this->getUser();
                $session = $this->getDoctrine()->getRepository(Sessions::class)->findOneBy(["id" => $id]);

                $user->addParticipe($session);

                //Prend l'id Session pour le mettre dans la table User-Session
                $session->addUser($user);
                //$session->getNbParticipants() = $session->getNbParticipants() + 1;

                $manager->persist($user);
                $manager->persist($session);
                $manager->flush();

                return new JsonResponse("Ca marche !");
            } else {
                return new Response('Error!', 400);
            }
        }

        return new Response('Error!', 400);
    }


    /**
     * @Route("/desinscrire-session/{id}", name="desinscrire")
     */
    public function removeSession(Request $request, EntityManagerInterface $manager, $id)
    {

        if ($request->isXMLHttpRequest()) {

            //Instanciation  de l'entity qui regroupe l'id Session et l'id User
            $user = $this->getUser();
            $session = $this->getDoctrine()->getRepository(Sessions::class)->findOneBy(["id" => $id]);

            $user->removeParticipe($session);

            //Prend l'id Session pour le mettre dans la table User-Session
            $session->removeUser($user);

            $manager->persist($user);
            $manager->persist($session);
            $manager->flush();

            return new JsonResponse("Ca marche !");
        }

        return new Response('Error!', 400);
    }
}
