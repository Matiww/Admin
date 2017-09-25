<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\SystemUser;
use AppBundle\Entity\Email;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/getEmails", name="get_emails")
     */
    public function getUserEmailsAction(Request $request) {
        $user_id = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();
        $user_additional_emails = $em->getRepository('AppBundle:Email')->findBy(array('user' => $user_id));
        $user = $em->getRepository('AppBundle:SystemUser')->find($user_id);
        $user_emails = array();
        if(!empty($user_additional_emails)) {
            foreach($user_additional_emails as $key => $value) {
                $user_emails['emails'][$key]['id'] = $value->getId();
                $user_emails['emails'][$key]['label'] = $value->getEmail();
            }
        } else {
            $user_emails['emails'] = array();
        }
        $user_emails['name'] = $user->getFirstname();
        $user_emails['email'] = $user->getEmail();
        return new JsonResponse($user_emails);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/deleteEmail/{id}", name="email_delete")
     */
    public function deleteEmail(Request $request, Email $id) {
        $em = $this->getDoctrine()->getManager();
        $email = $em->getRepository('AppBundle:Email')->find($id);
        $em->remove($email);
        $em->flush();

        return new JsonResponse('success');
    }

    /**
     * @return Response
     * @Route("/addEmail", name="add_email")
     */
    public function addEmailAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:SystemUser')->find($request->request->get('id'));
        $emails = $em->getRepository('AppBundle:Email')->findBy(array('user' => $request->request->get('id')));

        if(!empty($emails)) {
            foreach($emails as $value) {
                if(strtolower($value->getEmail()) === strtolower($request->request->get('email'))) {
                    return new JsonResponse(array('success' => false));
                    exit;
                }
            }
        }

        $email = new Email();
        $email->setEmail($request->request->get('email'));
        $email->setUser($user);

        $em->persist($email);
        $em->flush();

        return new JsonResponse(array('success' => true, 'label' => $email->getEmail(), 'id' => $email->getId()));
    }
}
