<?php



namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class SecurityController extends AbstractController
{


    #[Route(path: '/logout', name: 'app_logout', methods: ["GET"])]

    public function logout(): void
    {

        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall.');

    }

}
