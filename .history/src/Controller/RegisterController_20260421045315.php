<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\EmailOtp;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session,
        MailerInterface $mailer
    ): Response {

        $errors = [];
        $data = [
            'name' => '',
            'email' => '',
            'phone' => ''
        ];

        if ($request->isMethod('POST')) {

            $action = $request->request->get('action');

            $data['name'] = trim($request->request->get('name'));
            $data['email'] = trim($request->request->get('email'));
            $data['phone'] = trim($request->request->get('phone'));
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');
            $otpCode = $request->request->get('otp');

            // ================= SEND OTP =================
            if ($action === 'send_otp') {

                if (empty($data['email'])) {
                    $errors['email'] = "Email requis";
                }

                if ($password !== $confirm) {
                    $errors['confirm'] = "Mot de passe incorrect";
                }

                if (empty($errors)) {

                    $code = random_int(100000, 999999);

                    $otp = new EmailOtp();
                    $otp->setEmail($data['email']);
                    $otp->setCode((string)$code);
                    $otp->setExpiry(new \DateTime('+5 minutes'));

                    $em->persist($otp);
                    $em->flush();

                    // envoyer email
                    $email = (new Email())
                        ->from('your_email@gmail.com')
                        ->to($data['email'])
                        ->subject('Code OTP')
                        ->text("Votre code OTP est : $code");

                    $mailer->send($email);

                    // session temporaire
                    $session->set('pending_user', [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'password' => password_hash($password, PASSWORD_BCRYPT)
                    ]);
                }
            }

            // ================= REGISTER =================
            if ($action === 'register') {

                $pending = $session->get('pending_user');

                if (!$pending) {
                    $errors['otp'] = "Envoyer OTP d'abord";
                } else {

                    $otp = $em->getRepository(EmailOtp::class)->findOneBy([
                        'email' => $pending['email'],
                        'code' => $otpCode
                    ]);

                    if (!$otp || $otp->getExpiry() < new \DateTime()) {
                        $errors['otp'] = "OTP invalide ou expiré";
                    } else {

                        $user = new User();
                        $user->setFullName($pending['name']);
                        $user->setEmail($pending['email']);
                        $user->setPhone($pending['phone']);
                        $user->setPasswordHash($pending['password']);
                        $user->setRole(UserRole::USER);
                        $user->setCreatedAt(new \DateTimeImmutable());

                        $em->persist($user);
                        $em->remove($otp);
                        $em->flush();

                        $session->remove('pending_user');

                        return $this->redirectToRoute('app_login');
                    }
                }
            }
        }

        return $this->render('register/Register.html.twig', [
            'errors' => $errors,
            'data' => $data
        ]);
    }
}