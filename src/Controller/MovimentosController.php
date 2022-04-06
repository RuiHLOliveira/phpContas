<?php

namespace App\Controller;

use DateTime;
use App\Entity\Conta;
use App\Entity\ItemMovimento;
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

    private function totalizaMovimentosParaExibicao($movimentos) {
        $saldoInicial = 0;
        $saldoFinalPeriodo = 0;
        $totalEntradas = 0;
        $totalSaidas = 0; //já em negativo
        foreach ($movimentos as $key => $movimento) {
            if($movimento->getValor() > 0) {
                $totalEntradas += $movimento->getValor();
            } elseif($movimento->getValor() < 0){
                $totalSaidas += $movimento->getValor(); //já em negativo
            }
        }
        // $saldoInicial = $saldoConta - $totalEntradas + $totalSaidas; 
        $saldoFinalPeriodo = $saldoInicial + $totalEntradas + $totalSaidas;
        $saldoInicial = number_format($saldoInicial,2);
        $saldoFinalPeriodo = number_format($saldoFinalPeriodo,2);
        $totalEntradas = number_format($totalEntradas,2);
        $totalSaidas = number_format($totalSaidas,2);
        return compact('totalEntradas','totalSaidas','saldoInicial','saldoFinalPeriodo');
    }

    /**
     * @Route("/movimentos", methods={"GET","HEAD"})
     */
    public function index(Request $request): Response
    {
        try {
            $filtros = [];
            $orderby['data'] = 'asc';

            //PROCESSA ORDERBY
            $data = $request->get('order-data');
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

            // PROCESSA FILTRO DE MÊS
            $mes = $request->get('mes');
            if($mes != null && $mes != '') {
                $movimentosFiltrados = array_filter($movimentos, function($movimento) use ($mes) {
                    $data = $movimento->getData()->format('Y-m-d');
                    return strpos($data,$mes) !== false;
                });
                $movimentos = array_values($movimentosFiltrados);
            }

            $totais = $this->totalizaMovimentosParaExibicao($movimentos);
            foreach ($movimentos as $key => $value) {
                $movimentos[$key]->serializarItensMovimentos();
            }

            return new JsonResponse(compact('movimentos','totais'),200);

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
            if($requestData->itensMovimentos === null || count($requestData->itensMovimentos) == 0){
                throw new ValidationException('Itens is needed');
            }
            if($requestData->data === null || $requestData->data === ''){
                throw new ValidationException('Data is needed');
            }

            $dataMovimento = new DateTime($requestData->data);
            $valorMovimento = str_replace(',','.',$requestData->valor);

            $nomeLoja = $requestData->descricao !== null && $requestData->descricao !== '' ? $requestData->nomeLoja : '';

            $conta = $this->getDoctrine()
            ->getRepository(Conta::class)
            ->findOneBy([
                'id' => $requestData->idConta
            ]);
            
            $totalValorMovimento = 0;
            foreach ($requestData->itensMovimentos as $key => $itemMovimento) {
                $valor = str_replace(',','.', $itemMovimento->valor);
                $totalValorMovimento += $valor;
            }

            $movimento = new Movimento();
            $movimento->setDescricao($requestData->descricao);
            $movimento->setNomeLoja($nomeLoja);
            $movimento->setValor($totalValorMovimento);
            $movimento->setData($dataMovimento);
            $movimento->setConta($conta);
            $movimento->setCreatedAt(new DateTimeImmutable());
            $movimento->setUpdatedAt(new DateTimeImmutable());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($movimento);
            $entityManager->flush();


            foreach ($requestData->itensMovimentos as $key => $itemMovimento) {
                $valor = str_replace(',','.', $itemMovimento->valor);
                $itemMovimentoObj = new ItemMovimento();
                $itemMovimentoObj->setMovimento($movimento);
                $itemMovimentoObj->setValor($valor);
                $itemMovimentoObj->setNome($itemMovimento->nome);
                $entityManager->persist($itemMovimentoObj);
                $entityManager->flush();
            }

            $conta = $this->atualizaSaldoConta($conta);

            $movimento = $this->getDoctrine()->getRepository(Movimento::class)
                ->findOneBy([
                'id' => $movimento->getId()
            ]);
            $movimento->serializarItensMovimentos();

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
            if($requestData->itensMovimentos === null || count($requestData->itensMovimentos) == 0){
                throw new ValidationException('Itens is needed');
            }
            if($requestData->data === null || $requestData->data === ''){
                throw new ValidationException('Data is needed');
            }

            $dataMovimento = new DateTime($requestData->data);
            // $valorMovimento = str_replace(',','.',$requestData->valor);

            $nomeLoja = $requestData->descricao !== null && $requestData->descricao !== '' ? $requestData->nomeLoja : '';

            $movimento = $this->getDoctrine()
            ->getRepository(Movimento::class)
            ->findOneBy([
                'id' => $id
            ]);

            if($movimento == null) {
                throw new NotFoundHttpException('Movimento não encontrada.');
            }

            $totalValorMovimento = 0;
            foreach ($requestData->itensMovimentos as $key => $itemMovimento) {
                $valor = str_replace(',','.', $itemMovimento->valor);
                $totalValorMovimento += $valor;
            }

            $movimento->setDescricao($requestData->descricao);
            $movimento->setNomeLoja($nomeLoja);
            $movimento->setValor($totalValorMovimento);
            $movimento->setData($dataMovimento);
            // $movimento->setUpdatedAt(new DateTimeImmutable());

            $em = $this->getDoctrine()->getManager();
            $em->persist($movimento);

            $itensMovimentosAntigos = $this->getDoctrine()
            ->getRepository(ItemMovimento::class)
            ->findBy([
                'movimento' => $movimento
            ]);

            foreach ($itensMovimentosAntigos as $key => $itemMovimentoAntigo) {
                //localizar o item antigo no array de itens recebidos
                $itemMovimentoRecebido = array_filter($requestData->itensMovimentos, function($itemMovimentoRecebido) use ($itemMovimentoAntigo) {
                    return $itemMovimentoRecebido->id == $itemMovimentoAntigo->getId();
                });
                $itemMovimentoRecebido = array_values($itemMovimentoRecebido);
                if(count($itemMovimentoRecebido) > 0) {
                    $itemMovimentoRecebido = $itemMovimentoRecebido[0];
                    //se localizar, atualiza
                    $valor = str_replace(',','.', $itemMovimentoRecebido->valor);
                    $itemMovimentoAntigo->setValor($valor);
                    $itemMovimentoAntigo->setNome($itemMovimentoRecebido->nome);
                    $em->persist($itemMovimentoAntigo);
                } else {
                    //se não, deleta
                    $em->remove($itemMovimentoAntigo);
                }
            }

            foreach ($requestData->itensMovimentos as $key => $itemMovimentoNovo) {
                //localizar o item novo no array dos antigos, se não encontrar, inserir
                $encontrados = array_filter($itensMovimentosAntigos, function($itemMovimentoAntigo) use ($itemMovimentoNovo) {
                    return $itemMovimentoNovo->id == $itemMovimentoAntigo->getId();
                });
                if(empty($encontrados)){
                    //insere
                    $valor = str_replace(',','.', $itemMovimentoNovo->valor);
                    $itemMovimentoObj = new ItemMovimento();
                    $itemMovimentoObj->setMovimento($movimento);
                    $itemMovimentoObj->setValor($valor);
                    $itemMovimentoObj->setNome($itemMovimentoNovo->nome);
                    $em->persist($itemMovimentoObj);
                }
            }

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
            foreach ($movimento->getItensMovimentos() as $key => $item) {
                $em->remove($item);
            }
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
