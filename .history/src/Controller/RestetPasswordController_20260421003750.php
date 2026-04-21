<?php

namespace App\Controller\Modules\Samir;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset-password', name: 'app_reset_password')]
    public function index(Request $request): Response
    {
        $message = null;

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $otp = $request->request->get('otp');
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            if ($password !== $confirm) {
                $message = "Les mots de passe ne correspondent pas";
            } else {
                // logique reset (DB / mail / OTP)
                $message = "Mot de passe modifié avec succès";
            }
        }

        return $this->render('samir/Forgetpassword/ResetPassword.html.twig', [
            'message' => $message
        ]);
    }
}