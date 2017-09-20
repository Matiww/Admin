<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\SystemUser;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:SystemUser')->findAll();

        return $this->render('default/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/add", name="add")
     */
    public function addAction()
    {
        return $this->render('AppBundle:Default:add.html.twig');
    }

    /**
     * @Route("/add_user", name="add_user")
     */
    public function addUserAction(Request $request) {
        $firstName = $request->request->get('firstname');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $em = $this->getDoctrine()->getManager();

        $user = new SystemUser();
        $user->setFirstname($firstName);
        $user->setSurname($surname);
        $user->setEmail($email);
        $user->setPassword($password);

        $em->persist($user);

        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/edit/{id}", name="user_edit")
     */
    public function editAction(SystemUser $systemUser) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:SystemUser')->find($systemUser->getId());

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$user
            );
        }

        return $this->render('AppBundle:Default:edit.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user_edit/", name="user_edit_action")
     */
    public function editUserAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:SystemUser')->find($request->get('id'));
        $firstName = $request->request->get('firstname');
        $surname = $request->request->get('surname');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id '.$user
            );
        }

        $user->setFirstname($firstName);
        $user->setSurname($surname);
        $user->setEmail($email);
        $user->setPassword($password);

        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/delete/{id}", name="user_delete")
     */
    public function deleteUserAction(SystemUser $systemUser) {
        if (!$systemUser) {
            throw $this->createNotFoundException('No guest found');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($systemUser);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}
