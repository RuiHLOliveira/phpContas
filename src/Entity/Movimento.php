<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
            'nomeLoja' => $this->getNomeLoja(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
        
        if($this->serializarItensMovimentos == true){
            $array['itensMovimentos'] = $this->unpackItensMovimentosToArray($this->getItensMovimentos());
        }
        return $array;
    }

    private function unpackItensMovimentosToArray($collection){
        $itensMovimentos = $collection->toArray();
        foreach ($itensMovimentos as $key => $item) {
            // $movimento->setUser(null);
            $item->setMovimento(null);
            $itensMovimentos[$key] = $item;
        }
        return $itensMovimentos;
    }
    
    public function serializarItensMovimentos(){
        $this->serializarItensMovimentos = true;
    }

    public $serializarItensMovimentos = false;

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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomeLoja;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=ItemMovimento::class, mappedBy="movimento")
     */
    private $itensMovimentos;

    public function __construct()
    {
        $this->itensMovimentos = new ArrayCollection();
    }

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

    public function getNomeLoja(): ?string
    {
        return $this->nomeLoja;
    }

    public function setNomeLoja(?string $nomeLoja): self
    {
        $this->nomeLoja = $nomeLoja;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection|ItemMovimento[]
     */
    public function getItensMovimentos(): Collection
    {
        return $this->itensMovimentos;
    }

    public function addItensMovimento(ItemMovimento $itensMovimento): self
    {
        if (!$this->itensMovimentos->contains($itensMovimento)) {
            $this->itensMovimentos[] = $itensMovimento;
            $itensMovimento->setMovimento($this);
        }

        return $this;
    }

    public function removeItensMovimento(ItemMovimento $itensMovimento): self
    {
        if ($this->itensMovimentos->removeElement($itensMovimento)) {
            // set the owning side to null (unless already changed)
            if ($itensMovimento->getMovimento() === $this) {
                $itensMovimento->setMovimento(null);
            }
        }

        return $this;
    }
}
