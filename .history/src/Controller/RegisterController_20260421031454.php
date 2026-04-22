<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\EmailOtp;
use App\Enum\UserRole;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailService $mailService,
        SessionInterface $session
    ): Response {

        $errors = [];
        $data = ['name'=>'','email'=>'','phone'=>''];

        if ($request->isMethod('POST')) {

            $data['name'] = trim($request->request->get('name'));
            $data['email'] = trim($request->request->get('email'));
            $data['phone'] = trim($request->request->get('phone'));

            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            if (!$data['email']) $errors['email'] = "Email requis";
            if ($password !== $confirm) $errors['confirm'] = "Mots de passe différents";

            $exists = $em->getRepository(User::class)
                ->findOneBy(['email'=>$data['email']]);

            if ($exists) $errors['email'] = "Email déjà utilisé";

            if (empty($errors)) {

                // 🔥 stock temporaire user
                $session->set('pending_user', [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => password_hash($password, PASSWORD_BCRYPT)
                ]);

                // 🔐 OTP
                $otp = random_int(100000, 999999);

                $otpEntity = new EmailOtp();
                $otpEntity->setEmail($data['email']);
                $otpEntity->setCode($otp);
                $otpEntity->setExpiry(new \DateTime('+10 minutes'));

                $em->persist($otpEntity);
                $em->flush();

                // 📧 send email
                $mailService->sendOtp($data['email'], $otp);

                return $this->redirectToRoute('app_verify_otp');
            }
        }

        return $this->render('register/register.html.twig', [
            'errors' => $errors,
            'data' => $data
        ]);
    }
}