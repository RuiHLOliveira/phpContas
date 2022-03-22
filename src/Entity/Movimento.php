<?php

namespace App\Entity;

use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MovimentoRepository;

/**
 * @ORM\Entity(repositoryClass=MovimentoRepository::class)
 */
class Movimento implements JsonSerializable
{
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'descricao' => $this->getDescricao(),
            'valor' => $this->getValor(),
            'data' => $this->getData(),
            // 'createdAt' => $this->getCreatedAt(),
            // 'updatedAt' => $this->getUpdatedAt(),
        ];
        return $array;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descricao;

    /**
     * @ORM\Column(type="date")
     */
    private $data;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $valor;

    /**
     * @ORM\ManyToOne(targetEntity=Conta::class, inversedBy="movimentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $conta;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getConta(): ?Conta
    {
        return $this->conta;
    }

    public function setConta(?Conta $conta): self
    {
        $this->conta = $conta;

        return $this;
    }
}
