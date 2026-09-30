<?php

namespace App\Controller;

use App\Entity\Contacto;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ContactoFormType;
use Symfony\Component\Form\SubmitButton;


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

        // Devolvemos como respuesta el html
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
    }
    #[Route('/contacto/nuevo/{nombre}/{telefono}/{email}', name: 'nuevo-con-datos')]

    public function nuevoConDatos(
        ManagerRegistry $doctrine,
        string $nombre,
        string $telefono,
        string $email
    ) {
        // NUEVO: si no hay sesión, a la portada
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $contacto = new Contacto();
        $contacto->setNombre($nombre);
        $contacto->setTelefono($telefono);
        $contacto->setEmail($email);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($contacto);
        $entityManager->flush();

        return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
    }

    public function modificar(ManagerRegistry $doctrine, Request $request, int $codigo, string $nombre_nuevo)
    {
        $contacto = $doctrine->getRepository(Contacto::class)->find($codigo);

        if ($contacto) {
            $contacto->setNombre($nombre_nuevo);
            $entityManager = $doctrine->getManager();
            try {
                $entityManager->persist($contacto);
                $entityManager->flush();
                return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
            } catch (\Exception $e) {
                error_log("Error insertando objeto" . $e->getMessage());
                return new Response("Error insertando objeto" . $e->getMessage());
            }
        }
        return $this->redirectToRoute('contacto', ["codigo" => null]);
    }
    #[Route('/contacto/nuevo', name: 'nuevo')]
    public function nuevo(ManagerRegistry $doctrine, Request $request)
    {
        // NUEVO: si no hay sesión, a la portada
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $contacto = new Contacto();
        $formulario = $this->createForm(ContactoFormType::class, $contacto);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $contacto = $formulario->getData();
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contacto);
            $entityManager->flush();
            return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
        }
        return $this->render('nuevo.html.twig', array('formulario' => $formulario->createView()));
    }
    #[Route('/contacto/editar/{codigo}', name: 'editar', requirements: ["codigo" => "\d+"])]

    public function editar(ManagerRegistry $doctrine, Request $request, int $codigo)
    {
        // NUEVO: si no hay sesión, a la portada
        if (!$this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        //En este caso, los datos los obtenemos del repositorio de contactos
        $contacto = $repositorio->find($codigo);
        if ($contacto) {
            // A partir de $contacto, rellena automáticamente el formulario y el resto es igual que para nuevo
            $formulario = $this->createForm(ContactoFormType::class, $contacto);

            // NUEVO: botón Borrar (el de guardar es el que ya tiene ContactoFormType)
            $formulario->add('borrar', SubmitType::class, [
                'label' => 'Eliminar',
                'attr' => ['onclick' => "return confirm('¿Seguro que quieres eliminar este contacto?')"],
            ]);

            $formulario->handleRequest($request);

            if ($formulario->isSubmitted()) {
                /** @var SubmitButton $botonBorrar */
                $botonBorrar = $formulario->get('borrar');

                // ¿Ha pulsado Borrar?
                if ($botonBorrar->isClicked()) {
                    $entityManager->remove($contacto);
                    $entityManager->flush();
                    return $this->redirectToRoute('inicio');
                }

                // Si no, ha pulsado Guardar: guardamos y redirigimos a la ficha
                if ($formulario->isValid()) {
                    $entityManager->flush();
                    return $this->redirectToRoute('contacto', ["codigo" => $contacto->getId()]);
                }
            }
            // Ponemos los datos del contacto
            return $this->render('editar.html.twig', array(
                'formulario' => $formulario->createView()
            ));
        } else {
            return $this->render('contacto.html.twig', [
                'contacto' => NULL
            ]);
        }
    }

}