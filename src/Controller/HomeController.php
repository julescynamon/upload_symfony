<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('attachment', FileType::class, [
                'multiple' => true,
                'constraints' => [
                    new All(
                        new File([
                            'maxSize' => '1M',
                            'maxSizeMessage' => 'Le fichier {{ name }} fait {{ size }} {{ suffix }} et la limite est {{ limit }} {{ suffix }}.',
                            'mimeTypes' => [
                                'image/png'
                            ],
                            'mimeTypesMessage' => 'Le type du fichier ({{ type }}) est invalide. Les types requis sont {{ types }}.',
                        ])
                    )
                ]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $attachment */
            $attachments = $form->get('attachment')->getData();
            foreach ($attachments as $attachment) {
                $originalFilename = pathinfo($attachment->getClientOriginalName(), PATHINFO_FILENAME);
                $randomFileName = bin2hex(random_bytes(10));
                $extension = $attachment->guessExtension();
                $attachment->move('images', $originalFilename . '-' . $randomFileName . '.' . $extension);
            }
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
