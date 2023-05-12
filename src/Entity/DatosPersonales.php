<?php

namespace App\Entity;

use App\Repository\DatosPersonalesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DatosPersonalesRepository::class)]
class DatosPersonales
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'datosPersonales', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_usuario = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellidos = null;

    #[ORM\Column(nullable: true)]
    private ?int $edad = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localidad = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $direccion = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pais = null;

    #[ORM\Column(length: 5, options: ["default"=> "ES"])]
    private ?string $idioma_predefinido = null;

    public function getIdUsuario(): ?User
    {
        return $this->id_usuario;
    }

    public function setIdUsuario(User $id_usuario): self
    {
        $this->id_usuario = $id_usuario;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(?string $apellidos): self
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getEdad(): ?int
    {
        return $this->edad;
    }

    public function setEdad(?int $edad): self
    {
        $this->edad = $edad;

        return $this;
    }

    public function getLocalidad(): ?string
    {
        return $this->localidad;
    }

    public function setLocalidad(?string $localidad): self
    {
        $this->localidad = $localidad;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(?string $cp): self
    {
        $this->cp = $cp;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function getIdiomaPredefinido(): ?string
    {
        return $this->idioma_predefinido;
    }

    public function setIdiomaPredefinido(string $idioma_predefinido): self
    {
        $this->idioma_predefinido = $idioma_predefinido;

        return $this;
    }

    public function toArray(): array
    {
        $datosPersonalesArray = [
            'id_usuario' => $this->id_usuario,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'edad' => $this->edad,
            'localidad' => $this->localidad,
            'cp' => $this->cp,
            'direccion' => $this->direccion,
            'pais' => $this->pais,
            'idioma_predefinido' => $this->idioma_predefinido
        ];
        return $datosPersonalesArray;
    }

    public function fromJson($content): void
    {
        $content = json_decode($content, true);

        $this->id_usuario = $content['id_usuario'];
        $this->nombre = $content['nombre'];
        $this->apellidos = $content['apellidos'];
        $this->edad = $content['edad'];
        $this->localidad = $content["localidad"];
        $this->cp = $content["cp"];
        $this->direccion = $content["direccion"];
        $this->pais = $content["pais"];
        $this->idioma_predefinido = $content["idioma_predefinido"];       
    }
}
