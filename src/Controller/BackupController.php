<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use App\Entity\Conta;
use App\Entity\ItemMovimento;
use DateTimeImmutable;
use App\Entity\Movimento;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BackupController extends AbstractController
{


    private function sendMail($from, $to, $subject, $body, $attachments = []) {

        $username = $this->getParameter('email.email');
        $host = $this->getParameter('email.host');
        $port = $this->getParameter('email.port');
        $password = $this->getParameter('email.password');

        $mail = new PHPMailer; //From email address and name
        // $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;

        $mail->From = $from;
        $mail->addAddress($to);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = $subject;//"Subject Text";
        $mail->Body = $body;//"<i>Mail body in HTML</i>";

        foreach ($attachments as $key => $attach) {
            $mail->addAttachment($attach);
        }

        if (!$mail->send()) {
            throw new \Exception("Mailer Error: " . $mail->ErrorInfo);
        }
    }

    /**
     * @Route("/backup/export", name="backupExport")
     */
    public function export(Request $request): Response
    {
        try {
            // $user = $this->getUser();
            $contas = $this->getDoctrine()
            ->getRepository(Conta::class)
            // ->findBy(['user' => $user]);
            ->findAll();

            foreach ($contas as $key => $conta) {
                $conta->serializarMovimentos();
                $conta->serializarItensMovimentos();
            }

            $json = json_encode(compact('contas'));
            $hoje = new DateTime();
            $dataFormatada = $hoje->format('Y-m-dTH.i.s');
            $tempfilename = "$dataFormatada.phpcontas.export.json";// 2022-03-11T23 07 31.phpcontas.export
            $tempfilename = "../var/cache/$tempfilename";
            file_put_contents($tempfilename,$json);

            $attachments = [];
            $attachments[] = $tempfilename;

            $this->sendMail("ruigx@hotmail.com", "ruigx@hotmail.com", "Backup do PHPCONTAS", "<div>Este ?? o seu Backup</div>",$attachments);

            return new JsonResponse(compact('contas'), 200);
        } catch (\Exception $e) {
            //throw $th;
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 500);
        }
    }

    /**
     * @Route("/backup/import", name="backupImport")
     */
    public function import(Request $request): Response
    {
        try {
            // $user = $this->getUser();

            $file = $request->files->get('file');
            $mimetype = $file->getClientMimeType();
            $path = $file->getPathname();

            $data = file_get_contents($path);
            $data = json_decode($data,true);

            $entityManager = $this->getDoctrine()->getManager();

            foreach ($data['contas'] as $key => $conta) {

                $conta['nome'] .= ' bkp'; //padr??o backup

                $contaObj = new Conta();
                $contaObj->setNome($conta['nome']);
                $contaObj->setSaldo($conta['saldo']);
                
                $timezone = new DateTimeZone($conta['createdAt']['timezone']);
                $createdAt = new DateTime($conta['createdAt']['date'], $timezone);
                $contaObj->setCreatedAt($createdAt);

                $timezone = new DateTimeZone($conta['updatedAt']['timezone']);
                $updatedAt = new DateTime($conta['updatedAt']['date'], $timezone);
                $contaObj->setUpdatedAt($updatedAt);

                // $contaObj->setUser($user);

                $entityManager->persist($contaObj);
                $entityManager->flush();

                $contaObj->getId();

                foreach ($conta['movimentos'] as $key => $movimento) {
                    
                    // $movimento['descricao'] .= ' bkp'; //padr??o backup

                    $movimentoObj = new Movimento();
                    $movimentoObj->setDescricao($movimento['descricao']);
                    $movimentoObj->setValor($movimento['valor']);
                    $data = new DateTime($movimento['data']['date']);
                    $movimentoObj->setData($data);

                    if(isset($movimento['createdAt'])) {
                        $timezone = new DateTimeZone($movimento['createdAt']['timezone']);
                        $created_at = new DateTime($movimento['createdAt']['date'], $timezone);
                        $movimentoObj->setCreatedAt($created_at);
                    } else {
                        $movimentoObj->setCreatedAt(new DateTimeImmutable());
                    }
        
                    if(isset($movimento['updatedAt'])) {
                        $timezone = new DateTimeZone($movimento['updatedAt']['timezone']);
                        $updated_at = new DateTime($movimento['updatedAt']['date'], $timezone);
                        $movimentoObj->setUpdatedAt($updated_at);
                    } else {
                        $movimentoObj->setUpdatedAt(new DateTimeImmutable());
                    }
                    
                    $movimentoObj->setconta($contaObj);
                    // $movimentoObj->setUser($user);

                    $entityManager->persist($movimentoObj);
                    $entityManager->flush();

                    if(!isset($movimento['itensMovimentos']) || count($movimento['itensMovimentos']) == 0) {
                        $itemMovimentoObj = new ItemMovimento();
                        $itemMovimentoObj->setNome($movimento['descricao']);
                        $itemMovimentoObj->setValor($movimento['valor']);
                        $itemMovimentoObj->setMovimento($movimentoObj);
                        $entityManager->persist($itemMovimentoObj);
                        $entityManager->flush();
                    }

                    foreach ($movimento['itensMovimentos'] as $key => $itemMovimento) {
                        $itemMovimentoObj = new ItemMovimento();
                        $itemMovimentoObj->setNome($itemMovimento['nome']);
                        $itemMovimentoObj->setValor($itemMovimento['valor']);
                        $itemMovimentoObj->setMovimento($movimentoObj);
                        $entityManager->persist($itemMovimentoObj);
                        $entityManager->flush();
                    }
                }

            }

            $mensagem = "Backup successfully restored";

            return new JsonResponse(compact('mensagem'), 200);
            
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return new JsonResponse(compact('message'), 500);
        }
    }
}
