<?php

namespace App\Entity;

use App\Repository\CobroRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CobroRepository::class)]
class Cobro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creador = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receptor = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $creacion = null;

    #[ORM\Column(length: 255)]
    private ?string $titulo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $texto = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $revisado = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $completado = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fecha_completado = null;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $archivado = null;

    #[ORM\Column]
    private ?float $importe = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreador(): ?User
    {
        return $this->creador;
    }

    public function setCreador(User $creador): self
    {
        $this->creador = $creador;

        return $this;
    }

    public function getReceptor(): ?User
    {
        return $this->receptor;
    }

    public function setReceptor(User $receptor): self
    {
        $this->receptor = $receptor;

        return $this;
    }

    public function getCreacion(): ?\DateTimeInterface
    {
        return $this->creacion;
    }

    public function setCreacion(\DateTimeInterface $creacion): self
    {
        $this->creacion = $creacion;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getTexto(): ?string
    {
        return $this->texto;
    }

    public function setTexto(?string $texto): self
    {
        $this->texto = $texto;

        return $this;
    }

    public function isRevisado(): ?bool
    {
        return $this->revisado;
    }

    public function setRevisado(bool $revisado): self
    {
        $this->revisado = $revisado;

        return $this;
    }

    public function isCompletado(): ?bool
    {
        return $this->completado;
    }

    public function setCompletado(bool $completado): self
    {
        $this->completado = $completado;

        return $this;
    }

    public function getFechaCompletado(): ?\DateTimeInterface
    {
        return $this->fecha_completado;
    }

    public function setFechaCompletado(?\DateTimeInterface $fecha_completado): self
    {
        $this->fecha_completado = $fecha_completado;

        return $this;
    }

    public function isArchivado(): ?bool
    {
        return $this->archivado;
    }

    public function setArchivado(bool $archivado): self
    {
        $this->archivado = $archivado;

        return $this;
    }

    public function getImporte(): ?float
    {
        return $this->importe;
    }

    public function setImporte(float $importe): self
    {
        $this->importe = $importe;

        return $this;
    }

    public function toArray(): array
    {
        $cobrotArray = [
            'id' => $this->id,
            'creador' => $this->creador->getId(),
            'nombre_creador' => $this->creador->getDatosPersonales()->getNombre() ?: $this->creador->getEmail(),
            'receptor' => $this->receptor->getId(),
            'nombre_receptor' => $this->receptor->getDatosPersonales()->getNombre() ?: $this->receptor->getEmail(),
            'creacion' => $this->creacion->format('Y-m-d H:i'),
            'titulo' => $this->titulo,
            'texto' => $this->texto,
            'revisado' => $this->revisado,
            'completado' => $this->completado,
            'fecha_completado' => $this->fecha_completado ? $this->fecha_completado->format('Y-m-d H:i') : null,
            'archivado' => $this->archivado,
            'importe' => $this->importe
            
        ];
        return $cobrotArray;
    }

    public function fromJson($content): void
    {
        $content = json_decode($content, true);

        //$this->id = $content['id'];
        $this->creador = $content['creador'];
        $this->receptor = $content['receptor'];
        $this->creacion = DateTime::createFromFormat('Y-m-d H:i', $content['creacion']);
        $this->titulo = $content["titulo"];
        $this->texto = $content["texto"];
        $this->revisado = $content["revisado"];
        $this->completado = $content["completado"];
        $this->fecha_completado = DateTime::createFromFormat('Y-m-d H:i', $content['fecha_completado']);
        $this->archivado = $content["archivado"];
        $this->importe = $content["importe"];
        
    }


}