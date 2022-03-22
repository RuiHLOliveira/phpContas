<?php

namespace App\Controller;

use DateTime;
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

class MovimentosController extends AbstractController
{
    /**
     * @Route("/movimentos", methods={"GET","HEAD"})
     */
    public function index(Request $request): Response
    {
        try {
            $filtros = [];
            $orderby['data'] = 'asc';

            //PROCESSA ORDERBY
            $data = $request->get('data');
            if($data != null && ($data == 'asc' || $data == 'desc')) {
                $orderby['data'] = $data;
            }

            //PROCESSA FILTRO IDCONTA
            $idConta = $request->get('idConta');
            if($idConta != null) {
                $conta = $this->getDoctrine()->getRepository(Conta::class)->find($idConta);
                if($conta == null) {
                    throw new \Exception("Programe o algoritmo quando a conta não está definida.", 1);
                }
                $filtros['conta'] = $conta;
            }

            $movimentos = $this->getDoctrine()->getRepository(Movimento::class)->findBy($filtros, $orderby);

            return new JsonResponse(compact('movimentos'),200);

        } catch (ValidationException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    private function atualizaSaldoConta ($conta = null) {

        if($conta == null) {
            throw new \Exception("Programe o algoritmo quando a conta não está definida.", 1);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $movimentosParaTotalizar = $this->getDoctrine()->getRepository(Movimento::class)
        ->findBy([
            'conta' => $conta
        ]);

        $soma = 0;
        foreach ($movimentosParaTotalizar as $key => $movimentoParaTotalizar) {
            $soma += $movimentoParaTotalizar->getValor();
        }
        $conta->setSaldo($soma);
        $entityManager->persist($conta);
        $entityManager->flush();

        return $conta;
    }

    /**
     * @Route("/movimentos", methods={"POST"})
     */
    public function store(Request $request): Response
    {
        try {
            $requestData = json_decode($request->getContent());

            if($requestData->descricao === null || $requestData->descricao === ''){
                throw new ValidationException('Descricao is needed');
            }
            if($requestData->valor === null || $requestData->valor === ''){
                throw new ValidationException('Valor is needed');
            }
            if($requestData->data === null || $requestData->data === ''){
                throw new ValidationException('Data is needed');
            }

            $dataMovimento = new DateTime($requestData->data);
            $valorMovimento = str_replace(',','.',$requestData->valor);

            $conta = $this->getDoctrine()
            ->getRepository(Conta::class)
            ->findOneBy([
                'id' => $requestData->idConta
            ]);
            
            $movimento = new Movimento();
            $movimento->setDescricao($requestData->descricao);
            $movimento->setValor($valorMovimento);
            $movimento->setData($dataMovimento);
            $movimento->setConta($conta);
            // $movimento->setCreatedAt(new DateTimeImmutable());
            // $movimento->setUpdatedAt(new DateTimeImmutable());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($movimento);
            $entityManager->flush();

            $conta = $this->atualizaSaldoConta($conta);

            $movimento = $this->getDoctrine()->getRepository(Movimento::class)
                ->findOneBy([
                'id' => $movimento->getId()
            ]);

            return new JsonResponse(compact('movimento'),201);
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
     * @Route("/movimentos/{id}", methods={"PUT"})
     */
    public function update(Request $request, $id): Response
    {
        try {
            $requestData = json_decode($request->getContent());

            if($requestData->descricao === null || $requestData->descricao === ''){
                throw new ValidationException('Descricao is needed');
            }
            if($requestData->valor === null || $requestData->valor === ''){
                throw new ValidationException('Valor is needed');
            }
            if($requestData->data === null || $requestData->data === ''){
                throw new ValidationException('Data is needed');
            }

            $dataMovimento = new DateTime($requestData->data);
            $valorMovimento = str_replace(',','.',$requestData->valor);

            $movimento = $this->getDoctrine()
            ->getRepository(Movimento::class)
            ->findOneBy([
                'id' => $id
            ]);

            if($movimento == null) {
                throw new NotFoundHttpException('Movimento não encontrada.');
            }

            $movimento->setDescricao($requestData->descricao);
            $movimento->setValor($valorMovimento);
            $movimento->setData($dataMovimento);
            // $movimento->setUpdatedAt(new DateTimeImmutable());

            $em = $this->getDoctrine()->getManager();
            $em->persist($movimento);
            $em->flush();

            $conta = $this->getDoctrine()
            ->getRepository(Conta::class)
            ->findOneBy([
                'id' => $movimento->getConta()->getId()
            ]);

            $conta = $this->atualizaSaldoConta($conta);

            return new JsonResponse(compact('movimento'));
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
     * @Route("/movimentos/{id}", methods={"DELETE"})
     */
    public function destroy($id): Response
    {
        try {
            $movimento = $this->getDoctrine()->getRepository(Movimento::class)
                ->findOneBy([
                'id' => $id
            ]);

            if($movimento == null) {
                throw new NotFoundHttpException('Movimento not found.');
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($movimento);
            $em->flush();
            
            $conta = $this->getDoctrine()
            ->getRepository(Conta::class)
            ->findOneBy([
                'id' => $movimento->getConta()->getId()
            ]);
            
            $conta = $this->atualizaSaldoConta($conta);
            
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
