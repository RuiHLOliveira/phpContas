<?php

namespace App\Controller;

use App\Entity\Conta;
use DateTimeImmutable;
use App\Entity\Movimento;
use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Exception\InternalServerErrorHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContasController extends AbstractController
{
    
    /**
     * @Route("/contas/{id}", methods={"GET","HEAD"})
     */
    public function find(Request $request, $id): Response
    {
        try {
            $conta = $this->getDoctrine()->getRepository(Conta::class)->findOneBy(['id' => $id]);

            return new JsonResponse(compact('conta'),200);

        } catch (ValidationException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/contas", methods={"GET","HEAD"})
     */
    public function index(): Response
    {
        try {
            $contas = $this->getDoctrine()->getRepository(Conta::class)->findAll();

            return new JsonResponse(compact('contas'),200);

        } catch (ValidationException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/contas", methods={"POST"})
     */
    public function store(Request $request): Response
    {
        try {
            $requestData = json_decode($request->getContent());

            if($requestData->nome === null || $requestData->nome === ''){
                throw new ValidationException('Nome is needed');
            }

            $conta = new Conta();
            $conta->setNome($requestData->nome);
            $conta->setSaldo(0);
            $conta->setCreatedAt(new DateTimeImmutable());
            $conta->setUpdatedAt(new DateTimeImmutable());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($conta);
            $entityManager->flush();

            $conta = $this->getDoctrine()->getRepository(Conta::class)
                ->findOneBy([
                'id' => $conta->getId()
            ]);

            return new JsonResponse(compact('conta'),201);
        } catch (ValidationException $e) {
            // throw $e;
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 400);
        } catch (\Exception $e) {
            // throw $e;
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 500);
        }
    }
    
    
    /**
     * @Route("/contas/{id}", methods={"PUT"})
     */
    public function update(Request $request, $id): Response
    {
        try {
            $requestData = json_decode($request->getContent());

            $conta = $this->getDoctrine()->getRepository(Conta::class)
                ->findOneBy([
                    'id' => $id
            ]);
            
            if($conta == null) {
                throw new NotFoundHttpException('Conta não encontrada.');
            }

            $conta->setNome($requestData->nome);
            $conta->setUpdatedAt(new DateTimeImmutable());

            $em = $this->getDoctrine()->getManager();
            $em->persist($conta);
            $em->flush();

            return new JsonResponse(compact('conta'));
        } catch (NotFoundHttpException $e) {
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 404);
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 500);
        }
    }

    /**
     * @Route("/contas/{id}", methods={"DELETE"})
     */
    public function destroy($id): Response
    {
        try {
            $conta = $this->getDoctrine()->getRepository(Conta::class)
                ->findOneBy([
                'id' => $id
            ]);

            if($conta == null) {
                throw new NotFoundHttpException('Conta não encontrada.');
            }
            
            $movimentos = $this->getDoctrine()->getRepository(Movimento::class)->findBy(['conta' => $conta]);

            $em = $this->getDoctrine()->getManager();

            // remover movimentos da conta
            foreach ($movimentos as $key => $movimento) {
                foreach ($movimento->getItensMovimentos() as $key => $value) {
                    $em->remove($value);
                }
                $em->remove($movimento);
            }

            $em->remove($conta);
            $em->flush();
            
            return new JsonResponse();
        } catch (NotFoundHttpException $e) {
            $message = $e->getMessage();
            return new JsonResponse(compact('message'),404);
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
            $message = $e->getMessage();
            return new JsonResponse(compact('message'),500);
        }
    }
}
