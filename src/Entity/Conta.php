<?php

namespace App\Entity;

use App\Repository\ContaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=ContaRepository::class)
 */
class Conta implements JsonSerializable
{
    public function jsonSerialize()
    {
        $array = [
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'saldo' => $this->getSaldo(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];

        if($this->serializarMovimentos == true){
            $array['movimentos'] = $this->unpackMovimentosToArray($this->getMovimentos());
        }
        return $array;
    }

    private function unpackMovimentosToArray($collection){
        $movimentos = $collection->toArray();
        foreach ($movimentos as $key => $movimento) {
            if($this->serializarItensMovimentos == true){
                $movimento->serializarItensMovimentos();
            }
            // $movimento->setUser(null);
            $movimento->setConta(null);
            $movimentos[$key] = $movimento;
        }
        return $movimentos;
    }

    public function serializarMovimentos(){
        $this->serializarMovimentos = true;
    }
    public function serializarItensMovimentos(){
        $this->serializarItensMovimentos = true;
    }

    private $serializarMovimentos = false;
    private $serializarItensMovimentos = false;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
    private $saldo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\OneToMany(targetEntity=Movimento::class, mappedBy="conta")
     */
    private $movimentos;

    public function __construct()
    {
        $this->movimentos = new ArrayCollection();
    }

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

    public function getSaldo(): ?string
    {
        return $this->saldo;
    }

    public function setSaldo(string $saldo): self
    {
        $this->saldo = $saldo;

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
     * @return Collection|Movimento[]
     */
    public function getMovimentos(): Collection
    {
        return $this->movimentos;
    }

    public function addMovimento(Movimento $movimento): self
    {
        if (!$this->movimentos->contains($movimento)) {
            $this->movimentos[] = $movimento;
            $movimento->setIdConta($this);
        }

        return $this;
    }

    public function removeMovimento(Movimento $movimento): self
    {
        if ($this->movimentos->removeElement($movimento)) {
            // set the owning side to null (unless already changed)
            if ($movimento->getIdConta() === $this) {
                $movimento->setIdConta(null);
            }
        }

        return $this;
    }
}
