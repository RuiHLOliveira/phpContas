<?php

namespace App\Entity;

use App\Repository\ItemMovimentoRepository;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=ItemMovimentoRepository::class)
 */
class ItemMovimento implements JsonSerializable
{
    
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'valor' => $this->getValor(),
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
    private $nome;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $valor;

    /**
     * @ORM\ManyToOne(targetEntity=Movimento::class, inversedBy="itensMovimentos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movimento;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

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

    public function getMovimento(): ?Movimento
    {
        return $this->movimento;
    }

    public function setMovimento(?Movimento $movimento): self
    {
        $this->movimento = $movimento;

        return $this;
    }
}
