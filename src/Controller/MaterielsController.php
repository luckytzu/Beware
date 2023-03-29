<?php

namespace App\Controller;

use App\Repository\MaterielsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Materiels;
use App\Form\MaterielsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



class MaterielsController extends AbstractController
{
    #[Route('/materiels/index', name: 'materiels.index')]
    public function index(MaterielsRepository $repository): Response
    {

        return $this->render('pages/materiels/index.html.twig', [
            'donnees' => $repository->findAll()
        ]);
    }

    #[Route('/materiels/ajouter', 'materiels.ajout', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $materiels = new Materiels();
        $form = $this->createForm(MaterielsType::class,$materiels);
        $form ->add('Ajouter', SubmitType::class,[
            'attr' => [
                'class' => 'btn btn-primary mt-4',
            ],
            'label' => 'Créer mon matériel'
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $materiels = $form->getData();
            
            $manager->persist($materiels);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre matériel a été créer avec succès'
            );

            return $this->redirectToRoute('materiels.index');
        }
        
        return $this->render('pages/materiels/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/materiels/decrementer/{id}', 'materiels.moins', methods: ['GET','POST'])]
    public function moins(Materiels $materiels, EntityManagerInterface $manager, MaterielsRepository $repository, int $id,MailerInterface $mailer) : Response{
        $repository->findOneBy(["id" => $id]);

        $materiels->setQuantite($materiels->getQuantite()-1);
        
        $manager->persist($materiels);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre matériel a été modifier avec succès'
        );

        if($materiels->getQuantite()==0){
            $email = (new Email())
            ->from('siteweb@example.com')
            ->to('admin@example.com')
            ->subject('Le matériel '.$materiels->getNom().' est vide !')
            ->text("Il n'y a plus de quantité pour le matériel ".$materiels->getNom()." id=".$materiels->getId());

            $mailer->send($email);
        }

        return $this->redirectToRoute('materiels.index');
    }

    #[Route('/materiel/ajaxVoirMateriel/{id}', 'materiels.info.ajax', methods: ['POST'])]
    public function info(MaterielsRepository $repository, int $id, Materiels $materiels) : Response{
        $materiels = $repository->findOneBy(["id" => $id]);
        return new JsonResponse(['id'=>$materiels->getId(),'nom'=>$materiels->getNom(),'prix'=>$materiels->getPrix(),'quantite'=>$materiels->getQuantite(),'DateCreation'=>$materiels->getDateCreation()->format('d/m/Y')]);
    }

    #[Route('/materiels/modifier/{id}', 'materiels.edit', methods: ['GET','POST'])]
    //public function edit(MaterielsRepository $repository, int $id) : Response{
    public function edit(Materiels $materiels, Request $request, EntityManagerInterface $manager) : Response{
        //$Materiels = $repository->findOneBy(["id" => $id]);
        $form = $this->createForm(MaterielsType::class,$materiels);
        $form ->add('Modifier', SubmitType::class,[
            'attr' => [
                'class' => 'btn btn-primary mt-4',
            ],
            'label' => 'Modifier le matériel'
        ]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $materiels = $form->getData();
            
            $manager->persist($materiels);
            $manager->flush();

            //return $this->redirectToRoute('Materiels.index');

            $this->addFlash(
                'success',
                'Votre matériel a été modifié avec succès'
            );

            return $this->redirectToRoute('materiels.index');
        }

        return $this->render('pages/materiels/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/materiels/suppression/{id}', 'materiels.delete', methods: ['GET'])]
    public function delete(EntityManagerInterface $manager, Materiels $materiels) : Response{

        if(!$materiels){
            $this->addFlash(
                'success',
                'Votre matériel est inconnu'
            );
    
            return $this->redirectToRoute('materiels.index');
        }

        $manager->remove($materiels);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre matériel a été supprimer avec succès'
        );

        return $this->redirectToRoute('materiels.index');
    }

    #[Route('/materiels/pdf/{id}', 'materiels.pdf', methods: ['GET','POST'])]
    public function pdf(Materiels $materiels, EntityManagerInterface $manager, MaterielsRepository $repository, int $id) : Response{
        $materiels = $repository->findOneBy(["id" => $id]);
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(80);
        $pdf->Cell(30,10,'Materiel',1,0,'C');
        $pdf->Ln(20);
        $pdf->Cell(40,10,"Id : ".$materiels->getId(),0,1);
        $pdf->Cell(40,10,"Nom : ".$materiels->getNom(),0,1);
        $pdf->Cell(40,10,"Prix : ".$materiels->getPrix(),0,1);
        $pdf->Cell(40,10,"Quantite : ".$materiels->getQuantite(),0,1);
        $pdf->Cell(40,10,"Date de creation : ".$materiels->getDateCreation()->format('d/m/Y'),0,1);
        return new Response($pdf->Output(),200,array('Content-Type' => 'application/pdf'));
    }
}