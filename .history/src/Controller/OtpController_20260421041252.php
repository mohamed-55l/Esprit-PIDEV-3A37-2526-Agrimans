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

class OtpController extends AbstractController
{
    #[Route('/verify-otp', name: 'app_verify_otp')]
    public function verify(
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {

        if ($request->isMethod('POST')) {

            $code = $request->request->get('otp');
            $pending = $session->get('pending_user');

            if (!$pending) {
                return new Response("Session expirée");
            }

            $otp = $em->getRepository(EmailOtp::class)->findOneBy([
                'email' => $pending['email'],
                'code' => $code
            ]);

            if ($otp && $otp->getExpiry() > new \DateTime()) {

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

            return new Response("OTP invalide ou expiré");
        }

        return $this->render('register/verify_otp.html.twig');
    }
}