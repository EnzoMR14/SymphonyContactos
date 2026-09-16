<?php

namespace App\Controller;
use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactoController extends AbstractController
{

    #[Route('/contacto/{codigo}', name: 'contacto', requirements: ['codigo' => '[0-9]+'])]

    public function ficha(ManagerRegistry $doctrine, int $codigo = 1): Response
    {
        // La primera instrucción suele ser esta, ya que cogemos el repositorio de la entidad asociada
        $repositorio = $doctrine->getRepository(Contacto::class);
        // Ahora usamos uno de los métodos del repositorio
        $contacto = $repositorio->find($codigo);
        // Y creamos la vista HTML
        $html = "
    <h1>Detalle del contacto</h1>
    <p>Nombre: " . $contacto->getNombre() . "</p>
    <p>Teléfono: " . $contacto->getTelefono() . "</p>
    <p>Email: " . $contacto->getEmail() . "</p>
    ";
        // Devolvemos como respuesta el html
        return new Response($html);
    }

}
